<?php
namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class VenteLitre extends Model
{
    protected $table = 'vente_litres';

    protected $fillable = [
        'id_cuve',
        'qte_vendu',
        'volume',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'qte_vendu' => 'float',
        'status'    => 'boolean',
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
     * SCOPE : VISIBILITÉ DES VENTES
     * =================================================
     */

    // public function scopeVisible(Builder $query): Builder
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     // 🔥 Super admin : tout voir
    //     if ($user->role === 'super_admin') {
    //         return $query;
    //     }

    //     // 🔹 Toutes les autres règles passent par la visibilité des cuves
    //     return $query->whereHas('cuve', function ($q) {
    //         $q->visible(); // 👈 héritage DIRECT de Cuve::visible()
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
     * 🔹 PRIORITÉ : station active (middleware)
     * =================================================
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
    return $query->whereHas('cuve', function ($q) {
        $q->visible();
    });
}


    /**
     * =================================================
     * RELATIONS MÉTIER
     * =================================================
     */

    /**
     * Cuve concernée par la vente
     */
    public function cuve(): BelongsTo
    {
        return $this->belongsTo(Cuve::class, 'id_cuve');
    }

    /**
     * =================================================
     * RELATIONS AUDIT
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
