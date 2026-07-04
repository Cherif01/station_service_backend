<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class CompteDirection extends Model
{
    protected $table = 'comptes_direction';

    protected $fillable = [
        'numero',
        'libelle',
        'commentaire',
        'solde_initial',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'solde_initial' => 'float',
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
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function versements(): HasMany
    {
        return $this->hasMany(OperationCompte::class, 'id_compte_direction');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    /**
     * =================================================
     * MÉTIER : CALCUL DU SOLDE ACTUEL
     * =================================================
     * Somme de tous les versements effectifs reçus depuis les stations.
     */
    public function getSoldeActuelAttribute(): float
    {
        $solde = (float) $this->solde_initial;

        // Versements reçus : stations → direction (id_source non null)
        $recus = OperationCompte::where('id_compte_direction', $this->id)
            ->where('status', 'effectif')
            ->whereNotNull('id_source')
            ->sum('montant');

        // Versements émis : direction → stations (id_destination non null)
        $emis = OperationCompte::where('id_compte_direction', $this->id)
            ->where('status', 'effectif')
            ->whereNotNull('id_destination')
            ->sum('montant');

        $solde += (float) $recus;
        $solde -= (float) $emis;

        return round($solde, 2);
    }
}
