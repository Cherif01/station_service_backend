<?php

namespace App\Modules\Administration\Models;

use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Station;
use App\Modules\Settings\Models\Ville;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'telephone',
        'adresse',
        'image',
        'role',
        'id_ville',
        'id_station',
        'status',
        'password',
        'created_by',
        'modify_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status'            => 'boolean',
        ];
    }

    /**
     * =================================================
     * BOOT : AUDIT
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
     * SCOPE : VISIBILITÉ DES UTILISATEURS
     * =================================================
     */
    // public function scopeVisible(Builder $query): Builder
    // {
    //     $auth = Auth::user();

    //     if (! $auth) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     switch ($auth->role) {

    //         /**
    //          * 🔥 SUPER ADMIN
    //          * → voit tout
    //          */
    //         case 'super_admin':
    //             return $query;

    //         /**
    //          * 🔵 ADMIN
    //          * 🟡 GÉRANT
    //          * 🟣 SUPERVISEUR
    //          * → utilisateurs liés à la station
    //          */
    //         case 'admin':
    //         case 'gerant':
    //         case 'superviseur':

    //             $stationId = $auth->id_station
    //                 ?? optional($auth->activeAffectation())->id_station;

    //             if (! $stationId) {
    //                 return $query->whereRaw('1 = 0');
    //             }

    //             return $query->where(function (Builder $q) use ($stationId) {

    //                 // Utilisateurs créés pour la station
    //                 $q->where('id_station', $stationId)

    //                     // OU utilisateurs affectés à la station
    //                     ->orWhereHas('affectations', function (Builder $qa) use ($stationId) {
    //                         $qa->where('id_station', $stationId)
    //                             ->where('status', true);
    //                     });
    //             });

    //         /**
    //          * 🔴 POMPISTE
    //          * → même pompe si possible
    //          * → sinon même station
    //          */
    //         case 'pompiste':

    //             $affectation = $auth->activeAffectation();

    //             if (! $affectation) {
    //                 return $query->whereRaw('1 = 0');
    //             }

    //             // Priorité : même pompe
    //             if (! empty($affectation->id_pompe)) {
    //                 return $query->whereHas('affectations', function (Builder $q) use ($affectation) {
    //                     $q->where('id_pompe', $affectation->id_pompe)
    //                         ->where('status', true);
    //                 });
    //             }

    //             // Fallback : même station
    //             if (! empty($affectation->id_station)) {
    //                 return $query->whereHas('affectations', function (Builder $q) use ($affectation) {
    //                     $q->where('id_station', $affectation->id_station)
    //                         ->where('status', true);
    //                 });
    //             }

    //             return $query->whereRaw('1 = 0');

    //         /**
    //          * ❌ AUTRES CAS
    //          */
    //         default:
    //             return $query->whereRaw('1 = 0');
    //     }
    // }
    public function scopeVisible(Builder $query): Builder
{
    $auth = Auth::user();

    if (! $auth) {
        return $query->whereRaw('1 = 0');
    }

    /**
     * =================================================
     * 🔹 STATION ACTIVE (via middleware)
     * =================================================
     */
    $activeStationId = cache()->get('active_station_id');

    switch ($auth->role) {

        /**
         * 🔥 SUPER ADMIN
         */
        case 'super_admin':

            // 👉 S’il a activé une station, il agit comme un gérant
            if ($activeStationId) {
                return $query->where(function (Builder $q) use ($activeStationId) {

                    $q->where('id_station', $activeStationId)

                      ->orWhereHas('affectations', function (Builder $qa) use ($activeStationId) {
                          $qa->where('id_station', $activeStationId)
                             ->where('status', true);
                      });
                });
            }

            // 👉 Sinon : voit tout (comportement actuel inchangé)
            return $query;

        /**
         * 🔵 ADMIN
         * 🟡 GÉRANT
         * 🟣 SUPERVISEUR
         */
        case 'admin':
        case 'gerant':
        case 'superviseur':

            $stationId = $activeStationId
                ?? $auth->id_station
                ?? optional($auth->activeAffectation())->id_station;

            if (! $stationId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function (Builder $q) use ($stationId) {

                $q->where('id_station', $stationId)

                  ->orWhereHas('affectations', function (Builder $qa) use ($stationId) {
                      $qa->where('id_station', $stationId)
                         ->where('status', true);
                  });
            });

        /**
         * 🔴 POMPISTE
         */
        case 'pompiste':

            $affectation = $auth->activeAffectation();

            if (! $affectation) {
                return $query->whereRaw('1 = 0');
            }

            // Priorité : même pompe
            if (! empty($affectation->id_pompe)) {
                return $query->whereHas('affectations', function (Builder $q) use ($affectation) {
                    $q->where('id_pompe', $affectation->id_pompe)
                      ->where('status', true);
                });
            }

            // Fallback : même station
            if (! empty($affectation->id_station)) {
                return $query->whereHas('affectations', function (Builder $q) use ($affectation) {
                    $q->where('id_station', $affectation->id_station)
                      ->where('status', true);
                });
            }

            return $query->whereRaw('1 = 0');

        /**
         * ❌ AUTRES CAS
         */
        default:
            return $query->whereRaw('1 = 0');
    }
}


    /**
     * =================================================
     * SCOPE : POMPISTES DISPONIBLES
     * =================================================
     */
    public function scopePompistesDisponibles(Builder $query): Builder
    {
        return $query
            ->where('role', 'pompiste')
            ->whereDoesntHave('affectations', function (Builder $q) {
                $q->where('status', true);
            });
    }

    /**
     * =================================================
     * MÉTHODES MÉTIER
     * =================================================
     */

    /**
     * Affectation active de l'utilisateur
     */
    public function activeAffectation(): ?Affectation
    {
        return $this->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->first();
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    /**
     * Affectations
     */
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_user')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Station courante (via affectation active)
     */
    public function station()
    {
        return $this->hasOneThrough(
            Station::class,
            Affectation::class,
            'id_user',
            'id',
            'id',
            'id_station'
        )
            ->where('affectations.status', true)
            ->latest('affectations.created_at');
    }

    /**
     * Ville
     */
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    /**
     * Audit
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'modify_by');
    }
}
