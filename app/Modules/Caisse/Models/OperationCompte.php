<?php
namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OperationCompte extends Model
{
    protected $table = 'operations_comptes';

    protected $fillable = [
        'id_compte',
        'id_source',
        'id_destination',
        'id_type_operation',
        'montant',
        'reference',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'montant' => 'float',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + RÉFÉRENCE AUTO
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            // 🔹 Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Génération automatique de la référence
            if (empty($m->reference)) {
                /*
                 * Format :
                 * OPC-YYYYMMDD-XXXXXX
                 * ex : OPC-20251229-A9F3KQ
                 */
                $m->reference = 'OPC-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
            }
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPES
     * =================================================
     */
    public function scopeEffectif(Builder $query): Builder
    {
        return $query->where('status', 'effectif');
    }

    // public function scopeVisible(Builder $query): Builder
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     /**
    //      * 🔥 SUPER ADMIN → tout voir
    //      */
    //     if ($user->role === 'super_admin') {
    //         return $query;
    //     }

    //     /**
    //      * =================================================
    //      * 🔹 Station courante de l'utilisateur
    //      * =================================================
    //      */
    //     $stationId = $user->id_station ?? $user->affectations()
    //         ->where('status', true)
    //         ->latest('created_at')
    //         ->value('id_station');

    //     if (! $stationId) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     /**
    //      * =================================================
    //      * 🔎 VISIBILITÉ OPÉRATIONS
    //      * =================================================
    //      */
    //     return $query->where(function (Builder $q) use ($stationId) {

    //         /**
    //          * 🔴 CAS 1 : TRANSFERT ÉMIS
    //          * → station = source
    //          */
    //         $q->whereHas('source', function (Builder $qs) use ($stationId) {
    //             $qs->where('id_station', $stationId);
    //         })

    //         /**
    //          * 🟢 CAS 2 : TRANSFERT REÇU
    //          * → station = destination
    //          */
    //             ->orWhereHas('destination', function (Builder $qd) use ($stationId) {
    //                 $qd->where('id_station', $stationId);
    //             })

    //         /**
    //          * 🔵 CAS 3 : OPÉRATIONS SIMPLES
    //          * → station = compte
    //          */
    //             ->orWhereHas('compte', function (Builder $qc) use ($stationId) {
    //                 $qc->where('id_station', $stationId);
    //             });
    //     });
    // }

    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * =================================================
         * 🔹 PRIORITÉ ABSOLUE : STATION ACTIVE
         * =================================================
         */
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            return $query->where(function (Builder $q) use ($stationActiveId) {

                // 🔴 Transfert émis
                $q->whereHas('source', function (Builder $qs) use ($stationActiveId) {
                    $qs->where('id_station', $stationActiveId);
                })

                // 🟢 Transfert reçu
                    ->orWhereHas('destination', function (Builder $qd) use ($stationActiveId) {
                        $qd->where('id_station', $stationActiveId);
                    })

                // 🔵 Opération simple
                    ->orWhereHas('compte', function (Builder $qc) use ($stationActiveId) {
                        $qc->where('id_station', $stationActiveId);
                    });
            });
        }

        /**
         * =================================================
         * 🔥 SUPER ADMIN (sans station active)
         * =================================================
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * =================================================
         * 🔵 ADMIN / 🟡 GÉRANT / 🟣 SUPERVISEUR
         * → station via affectation active
         * =================================================
         */
        $stationId = $user->id_station ?? $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($stationId) {

            $q->whereHas('source', function (Builder $qs) use ($stationId) {
                $qs->where('id_station', $stationId);
            })

                ->orWhereHas('destination', function (Builder $qd) use ($stationId) {
                    $qd->where('id_station', $stationId);
                })

                ->orWhereHas('compte', function (Builder $qc) use ($stationId) {
                    $qc->where('id_station', $stationId);
                });
        });
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_source');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_destination');
    }

    public function typeOperation(): BelongsTo
    {
        return $this->belongsTo(TypeOperation::class, 'id_type_operation');
    }

    /**
     * =================================================
     * AUDIT
     * =================================================
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
