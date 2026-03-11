<?php
namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ApprovisionnementCuve extends Model
{
    protected $table = 'approvisionnement_cuves';

    protected $fillable = [
        'id_cuve',
        'qte_appro',
        'type_appro',
        'id_fournisseur',
        'pu_unitaire',
        'commentaire',
        'created_by',
    ];

    protected $casts = [
        'qte_appro'   => 'float',
        'pu_unitaire' => 'float',
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
    }

    /**
     * =========================
     * SCOPE : VISIBILITÉ
     * (basé sur la DERNIÈRE affectation active)
     * =========================
     */

//     public function scopeVisible(Builder $query): Builder
// {
//     $user = Auth::user();

//     if (! $user) {
//         return $query->whereRaw('1 = 0');
//     }

//     // 🔥 Super admin : tout voir
//     if ($user->role === 'super_admin') {
//         return $query;
//     }

//     // 🔹 Héritage direct de la visibilité des cuves
//     return $query->whereHas('cuve', function (Builder $q) {
//         $q->visible();
//     });
// }

    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * 🔹 PRIORITÉ ABSOLUE : station active (middleware)
         */
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            return $query->whereHas('cuve', function (Builder $q) use ($stationActiveId) {
                $q->where('id_station', $stationActiveId);
            });
        }

        /**
         * 🔥 SUPER ADMIN (sans station active)
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * 🔹 Héritage direct de la visibilité des cuves
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
    public function cuve(): BelongsTo
    {
        return $this->belongsTo(Cuve::class, 'id_cuve');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
