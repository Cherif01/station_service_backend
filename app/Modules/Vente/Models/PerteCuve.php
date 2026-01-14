<?php
namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PerteCuve extends Model
{
    protected $table = 'pertes_cuves';

    protected $fillable = [
        'id_cuve',
        'quantite_perdue',
        'commentaire',
        'created_by',
        'modify_by',
    ];

    /**
     * =========================
     * BOOT : AUDIT
     * =========================
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
     * =========================
     * SCOPE : VISIBILITÉ PAR RÔLE
     * (ALIGNÉ À LigneVente)
     * =========================
     */
    // public function scopeVisible(Builder $query): Builder
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     switch ($user->role) {

    //         /**
    //          * 🔥 SUPER ADMIN
    //          * → accès total
    //          */
    //         case 'super_admin':
    //             return $query;

    //         /**
    //          * 🔵 ADMIN / 🟣 SUPERVISEUR / 🟡 GÉRANT
    //          * → pertes des cuves de la station
    //          *   issue de la DERNIÈRE affectation active
    //          */
    //         case 'admin':
    //         case 'superviseur':
    //         case 'gerant':

    //             $stationId = $user->affectations()
    //                 ->where('status', true)
    //                 ->latest('created_at')
    //                 ->value('id_station');

    //             if (! $stationId) {
    //                 return $query->whereRaw('1 = 0');
    //             }

    //             return $query->whereHas('cuve', function (Builder $q) use ($stationId) {
    //                 $q->whereHas('ligneVentes', function (Builder $lv) use ($stationId) {
    //                     $lv->where('id_station', $stationId);
    //                 });
    //             });

    //         /**
    //          * 🔴 POMPISTE
    //          * → uniquement les pertes
    //          *   des cuves utilisées dans SES ventes
    //          *   via son AFFECTATION ACTIVE
    //          */
    //         case 'pompiste':

    //             $affectationId = $user->affectations()
    //                 ->where('status', true)
    //                 ->latest('created_at')
    //                 ->value('id');

    //             if (! $affectationId) {
    //                 return $query->whereRaw('1 = 0');
    //             }

    //             return $query->whereHas('cuve', function (Builder $q) use ($affectationId) {
    //                 $q->whereHas('ligneVentes', function (Builder $lv) use ($affectationId) {
    //                     $lv->where('id_affectation', $affectationId);
    //                 });
    //             });

    //         /**
    //          * ❌ AUTRES CAS
    //          */
    //         default:
    //             return $query->whereRaw('1 = 0');
    //     }
    // }

    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * =================================================
         * 🔹 PRIORITÉ ABSOLUE : station active (middleware)
         * =================================================
         */
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            return $query->whereHas('cuve', function (Builder $q) use ($stationActiveId) {
                $q->where('id_station', $stationActiveId);
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
         * 🔹 HÉRITAGE DIRECT DE LA VISIBILITÉ DES CUVES
         * =================================================
         */
        return $query->whereHas('cuve', function (Builder $q) {
            $q->visible();
        });
    }

    /**
     * =========================
     * RELATIONS
     * =========================
     */

    /**
     * 🔹 Cuve concernée
     */
    public function cuve()
    {
        return $this->belongsTo(
            Cuve::class,
            'id_cuve'
        );
    }

    /**
     * 🔹 Audit
     */
    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function modifiedBy()
    {
        return $this->belongsTo(
            User::class,
            'modify_by'
        );
    }
}
