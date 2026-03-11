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
        $activeStationId = request()->attributes->get('station_active_id');

        switch ($auth->role) {

            /**
                 * 🔥 SUPER ADMIN
                 * → voit tout si aucune station active
                 * → sinon agit comme un gérant de la station active
                 */
            case 'super_admin':

                if ($activeStationId) {
                    return $query->where(function (Builder $q) use ($activeStationId) {
                        $q->where('id_station', $activeStationId)
                            ->orWhereHas('affectations', function (Builder $qa) use ($activeStationId) {
                                $qa->where('id_station', $activeStationId)
                                    ->where('status', true);
                            });
                    });
                }

                return $query;

            /**
                 * 🔵 ADMIN
                 * 🟡 GÉRANT
                 * 🟣 SUPERVISEUR
                 * → utilisateurs liés à la station active
                 */
            case 'admin':
            case 'gerant':
            case 'superviseur':

                $stationId = $activeStationId ?? $auth->id_station ?? optional($auth->activeAffectation())->id_station;

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
                 * → même pompe si possible
                 * → sinon même station
                 */
            case 'pompiste':

                $affectation = $auth->activeAffectation();

                if (! $affectation) {
                    return $query->whereRaw('1 = 0');
                }

                if (! empty($affectation->id_pompe)) {
                    return $query->whereHas('affectations', function (Builder $q) use ($affectation) {
                        $q->where('id_pompe', $affectation->id_pompe)
                            ->where('status', true);
                    });
                }

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
    public function activeAffectation(): ?Affectation
    {
        return Affectation::where('id_user', $this->id)
            ->where('status', true)
            ->latest('created_at')
            ->first();
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_user')
            ->orderBy('created_at', 'desc');
    }

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

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'modify_by');
    }
}
