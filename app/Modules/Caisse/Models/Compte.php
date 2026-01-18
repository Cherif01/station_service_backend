<?php
namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Compte extends Model
{
    protected $table = 'comptes';

    protected $fillable = [
        'id_station',
        'libelle',
        'numero',
        'commentaire',
        'solde_initial',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'solde_initial' => 'float',
    ];

    /**
     * =================================================
     * BOOT : AUDIT
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (Auth::check()) {
                $m->created_by = Auth::id();
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

    /**
     * 🔹 Visibilité par station (comme tout ton projet)
     */
    // public function scopeVisible(Builder $query): Builder
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     if ($user->role === 'super_admin') {
    //         return $query;
    //     }

    //     $stationId = $user->affectations()
    //         ->where('status', true)
    //         ->latest('created_at')
    //         ->value('id_station');

    //     if (! $stationId) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where('id_station', $stationId);
    // }
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * =================================================
         * 🔹 PRIORITÉ : station active (middleware)
         * =================================================
         */
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            return $query->where('id_station', $stationActiveId);
        }

        /**
         * 🔥 SUPER ADMIN (sans station active)
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * 🔵 ADMIN / 🟡 GÉRANT / 🟣 SUPERVISEUR
         * → station issue de l’affectation active
         */
        $stationId = $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id_station', $stationId);
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(OperationCompte::class, 'id_compte');
    }

    /**
     * =================================================
     * MÉTIER : CALCUL DU SOLDE ACTUEL
     * =================================================
     */
    public function getSoldeActuelAttribute(): float
    {
        $solde = (float) $this->solde_initial;

        $operations = OperationCompte::where('status', 'effectif')
            ->where(function ($q) {
                $q->where('id_compte', $this->id)
                    ->orWhere('id_source', $this->id)
                    ->orWhere('id_destination', $this->id);
            })
            ->with('typeOperation')
            ->get();

        foreach ($operations as $op) {

            $nature  = (int) $op->typeOperation->nature;
            $montant = (float) $op->montant;

            // =============================
            // 🔹 ENTRÉE
            // =============================
            if ($nature === 1 && (int) $op->id_compte === (int) $this->id) {
                $solde += $montant;
            }

            // =============================
            // 🔹 SORTIE
            // =============================
            if ($nature === 0 && (int) $op->id_compte === (int) $this->id) {
                $solde -= $montant;
            }

            // =============================
            // 🔹 TRANSFERT
            // =============================
            if ($nature === 2) {

                // source
                if ((int) $op->id_source === (int) $this->id) {
                    $solde -= $montant;
                }

                // destination
                if ((int) $op->id_destination === (int) $this->id) {
                    $solde += $montant;
                }
            }
        }

        return round($solde, 2);
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
