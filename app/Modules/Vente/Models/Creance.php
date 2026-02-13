<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Creance extends Model
{
    protected $table = 'creances';

    protected $fillable = [
        'id_init_vente',
        'date',
        'quantite',
        'prix_unitaire',
        'montant',
        'commentaire',
        'created_by',
        'modify_by',
    ];

    /**
     * ======================================
     * 🔹 EVENTS
     * ======================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 sécurité calcul montant
            $m->montant = (float) $m->quantite * (float) $m->prix_unitaire;
        });

        static::updating(function ($m) {

            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }

            // 🔹 recalcul automatique
            $m->montant = (float) $m->quantite * (float) $m->prix_unitaire;
        });
    }

    /**
     * ======================================
     * 🔹 SCOPE VISIBILITÉ
     * ======================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $stationActiveId = request()->attributes->get('station_active_id');

        if (! $stationActiveId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('initVente.client', function (Builder $q) use ($stationActiveId) {
            $q->where('id_station', $stationActiveId);
        });
    }

    /**
     * ======================================
     * 🔹 RELATIONS
     * ======================================
     */

    // 🔹 Vente liée
    public function initVente()
    {
        return $this->belongsTo(InitVente::class, 'id_init_vente');
    }

    // 🔹 Audit
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
