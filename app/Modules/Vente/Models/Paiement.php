<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Caisse\Models\Compte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'id_creance',
        'id_compte',
        'montant_payer',
        'mode_paiement',
        'created_by',
    ];

    protected $casts = [
        'montant_payer' => 'float',
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
            return $query->whereHas('creance', function ($q) use ($stationActiveId) {
                $q->where('id_station', $stationActiveId);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * =========================
     * RELATIONS
     * =========================
     */
    public function creance(): BelongsTo
    {
        return $this->belongsTo(Creance::class, 'id_creance');
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
