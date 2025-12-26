<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Pompe extends Model
{
    protected $table = 'pompes';

    protected $fillable = [
        'libelle',
        'reference',
        'type_pompe',
        'index_initial',
        'id_station',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : filtrage global + audit + référence
     * =================================================
     */
    protected static function booted(): void
    {
        /*
        |--------------------------------------------------------------------------
        | GLOBAL SCOPE : VISIBILITÉ DES POMPES
        |--------------------------------------------------------------------------
        */
        static::addGlobalScope('role_scope', function (Builder $query) {

            $user = Auth::user();

            // Aucun utilisateur connecté → aucune donnée
            if (! $user) {
                $query->whereRaw('1 = 0');
                return;
            }

            switch ($user->role) {

                /**
                 * 🔥 SUPER ADMIN
                 * → voit toutes les pompes
                 */
                case 'super_admin':
                    break;

                /**
                 * 🔵 ADMIN / SUPERVISEUR
                 * → pompes des stations de leur ville
                 */
                case 'admin':
                case 'superviseur':

                    if (! $user->station) {
                        $query->whereRaw('1 = 0');
                        return;
                    }

                    $query->whereHas('station', function ($q) use ($user) {
                        $q->where('id_ville', $user->station->id_ville);
                    });
                    break;

                /**
                 * 🟡 GÉRANT
                 * → pompes de sa station
                 */
                case 'gerant':

                    if (! $user->id_station) {
                        $query->whereRaw('1 = 0');
                        return;
                    }

                    $query->where('id_station', $user->id_station);
                    break;

                /**
                 * 🔴 POMPISTE
                 * → aucune pompe (accès via affectations uniquement)
                 */
                default:
                    $query->whereRaw('1 = 0');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CRÉATION : audit + référence automatique
        |--------------------------------------------------------------------------
        */
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Génération automatique de la référence pompe
            if (empty($m->reference)) {
                $nextId = self::withoutGlobalScopes()->max('id') + 1;
                $m->reference = 'PMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | MISE À JOUR : audit
        |--------------------------------------------------------------------------
        */
        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * ============================
     * RELATIONS
     * ============================
     */

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
