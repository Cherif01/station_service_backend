<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PerteCuve extends Model
{
    protected $table = 'pertes_cuves';

    protected $fillable = [
        'id_station',
        'id_cuve',
        'quantite_perdue',
        'commentaire',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'quantite_perdue' => 'float',
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

            // station automatique via middleware
            if (! $m->id_station) {
                $m->id_station = request()->attributes->get('station_active_id');
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
     * SCOPE : VISIBILITÉ
     * =========================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $stationActiveId = request()->attributes->get('station_active_id');

        /**
         * 🔥 SUPER ADMIN
         */
        if ($user->role === 'super_admin' && ! $stationActiveId) {
            return $query;
        }

        /**
         * 🔹 Filtrage par station active
         */
        if ($stationActiveId) {
            return $query->where('id_station', $stationActiveId);
        }

        return $query->whereRaw('1 = 0');
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

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}