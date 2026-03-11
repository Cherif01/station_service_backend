<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Cuve extends Model
{
    protected $table = 'cuves';

    protected $fillable = [
        'libelle',
        'pu_vente',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'pu_vente' => 'float',
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
     * RELATIONS AUDIT
     * =================================================
     */
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
     * RELATION LIGNE VENTES
     * =================================================
     */
    public function ligneVentes()
    {
        return $this->hasMany(LigneVente::class, 'id_cuve');
    }
}