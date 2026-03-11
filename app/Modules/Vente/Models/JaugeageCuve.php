<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Vente\Models\Cuve;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class JaugeageCuve extends Model
{
    protected $table = 'jaugeage_cuves';

    protected $fillable = [
        'id_cuve',
        'hauteur',
        'volume_mesure',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'hauteur'       => 'float',
        'volume_mesure' => 'float',
        'status'        => 'boolean',
    ];

    /**
     * =================================================
     * AUDIT
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

        });

        static::updating(function ($model) {

            if (Auth::check()) {
                $model->modify_by = Auth::id();
            }

        });
    }

    /**
     * =================================================
     * SCOPE : VISIBILITÉ
     * =================================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * 🔹 Station active (middleware)
         */
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {

            return $query->whereHas('cuve', function (Builder $q) use ($stationActiveId) {

                $q->where('id_station', $stationActiveId);

            });

        }

        /**
         * 🔥 Super admin
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * 🔹 Héritage visibilité Cuve
         */
        return $query->whereHas('cuve', function (Builder $q) {

            $q->visible();

        });
    }

    /**
     * =================================================
     * RELATION CUVE
     * =================================================
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