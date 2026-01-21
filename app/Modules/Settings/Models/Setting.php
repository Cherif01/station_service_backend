<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'id_station',
        'cle',
        'valeur',
        'description',
        'created_by',
        'updated_by',
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
                $m->updated_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPE : VISIBILITÉ
     * =================================================
     * - station active → settings station
     * - fallback possible vers global
     */
    public function scopeVisible(Builder $query): Builder
    {
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            return $query->where(function ($q) use ($stationActiveId) {
                $q->where('id_station', $stationActiveId)
                  ->orWhereNull('id_station');
            });
        }

        // Sans station active → uniquement global
        return $query->whereNull('id_station');
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function station()
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * =================================================
     * HELPERS MÉTIER
     * =================================================
     */

    /**
     * 🔹 Récupérer une valeur de setting
     * (priorité station → global)
     */
    public static function getValue(string $cle, $default = null)
    {
        $stationActiveId = request()->attributes->get('station_active_id');

        if ($stationActiveId) {
            $value = self::where('cle', $cle)
                ->where('id_station', $stationActiveId)
                ->value('valeur');

            if ($value !== null) {
                return $value;
            }
        }

        return self::where('cle', $cle)
            ->whereNull('id_station')
            ->value('valeur') ?? $default;
    }

    /**
     * 🔹 Définir / mettre à jour un setting
     */
    public static function setValue(string $cle, $valeur, ?int $idStation = null, ?string $description = null): self
    {
        return self::updateOrCreate(
            [
                'cle'        => $cle,
                'id_station' => $idStation,
            ],
            [
                'valeur'      => $valeur,
                'description' => $description,
            ]
        );
    }
}
