<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Creance extends Model
{
    protected $table = 'creances';

    protected $fillable = [
        'id_station',
        'id_client',
        'id_pompe',
        'qte',
        'prix_unitaire',
        'montant',
        'commentaire',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'qte'           => 'float',
        'prix_unitaire' => 'float',
        'montant'       => 'float',
    ];

    /**
     * =========================
     * BOOT : AUDIT + CALCUL
     * =========================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }
            if (! $m->id_station) {
                $m->id_station = request()->attributes->get('station_active_id');
            }
            $m->montant = (float) $m->qte * (float) $m->prix_unitaire;
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
            $m->montant = (float) $m->qte * (float) $m->prix_unitaire;
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

        if ($user->role === 'super_admin' && ! $stationActiveId) {
            return $query;
        }

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
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function pompe(): BelongsTo
    {
        return $this->belongsTo(Pompe::class, 'id_pompe');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'id_creance');
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
