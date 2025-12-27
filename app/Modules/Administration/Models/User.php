<?php
namespace App\Modules\Administration\Models;

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
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'telephone',
        'adresse',
        'image',
        'role',
        'id_station',
        'id_ville',
        'status',
        'password',

        // audit
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
     * BOOT : filtrage global par rôle + audit
     * =================================================
     */
    protected static function booted(): void
    {
        /*
        |--------------------------------------------------------------------------
        | GLOBAL SCOPE : VISIBILITÉ DES UTILISATEURS
        |--------------------------------------------------------------------------
        */
        // static::addGlobalScope('role_scope', function (Builder $query) {

        //     $auth = Auth::user();

        //     // Aucun utilisateur connecté → aucune donnée
        //     if (! $auth) {
        //         $query->whereRaw('1 = 0');
        //         return;
        //     }

        //     switch ($auth->role) {

        //         /**
        //          * 🔥 SUPER ADMIN
        //          * → voit tous les utilisateurs
        //          */
        //         case 'super_admin':
        //             break;

        //         /**
        //          * 🔵 ADMIN / SUPERVISEUR
        //          * → utilisateurs des stations de leur ville
        //          */
        //         case 'admin':
        //         case 'superviseur':

        //             if (! $auth->station) {
        //                 $query->whereRaw('1 = 0');
        //                 return;
        //             }

        //             $query->whereHas('station', function ($q) use ($auth) {
        //                 $q->where('id_ville', $auth->station->id_ville);
        //             });
        //             break;

        //         /**
        //          * 🟡 GÉRANT
        //          * → utilisateurs de SA station
        //          */
        //         case 'gerant':

        //             if (! $auth->id_station) {
        //                 $query->whereRaw('1 = 0');
        //                 return;
        //             }

        //             $query->where('id_station', $auth->id_station);
        //             break;

        //         /**
        //          * 🔴 POMPISTE
        //          * → uniquement lui-même
        //          */
        //         case 'pompiste':
        //             $query->where('id', $auth->id);
        //             break;

        //         /**
        //          * ❌ AUTRES CAS
        //          */
        //         default:
        //             $query->whereRaw('1 = 0');
        //     }
        // });

        /*
        |--------------------------------------------------------------------------
        | AUDIT AUTOMATIQUE
        |--------------------------------------------------------------------------
        */
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

    public function scopeVisible(Builder $query)
    {
        $auth = Auth::user();

        // 🔒 Aucun utilisateur → aucune donnée
        if (! $auth) {
            return $query->whereRaw('1 = 0');
        }

        switch ($auth->role) {

            /**
                 * 🔥 SUPER ADMIN
                 * → accès total
                 */
            case 'super_admin':
                return $query;

            /**
                 * 🔵 SUPERVISEUR
                 * → données des stations de sa ville
                 */
            case 'superviseur':

                if (! $auth->id_ville) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('station', function ($q) use ($auth) {
                    $q->where('id_ville', $auth->id_ville);
                });

            /**
                 * 🟡 ADMIN / GÉRANT
                 * → uniquement leur station
                 */
            case 'admin':
            case 'gerant':

                if (! $auth->id_station) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_station', $auth->id_station);

            /**
                 * 🔴 POMPISTE
                 * → bloqué par défaut
                 * (doit passer par Affectation ou relation dédiée)
                 */
            default:
                return $query->whereRaw('1 = 0');
        }
    }

    /**
     * ============================
     * Relations métier
     * ============================
     */

    // Station à laquelle l’utilisateur appartient
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    /**
     * ============================
     * Relations audit (STANDARD)
     * ============================
     */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'modify_by');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

}
