<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class JaugeageCuve extends Model
{
    protected $table = 'jaugeage_cuves';

    protected $fillable = [
        'id_station',
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
     * AUDIT + STATION AUTO
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            // station automatique via middleware
            if (! $model->id_station) {
                $model->id_station = request()->attributes->get('station_active_id');
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

        $stationActiveId = request()->attributes->get('station_active_id');

        /**
         * 🔥 Super Admin sans station active
         */
        if ($user->role === 'super_admin' && ! $stationActiveId) {
            return $query;
        }

        /**
         * 🔹 Filtrage par station
         */
        if ($stationActiveId) {
            return $query->where('id_station', $stationActiveId);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    public function cuve(): BelongsTo
    {
        return $this->belongsTo(Cuve::class, 'id_cuve');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
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