<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'id_init_vente',
        'montant_payer',
        'mode_paiement',
        'created_by',
    ];

    protected $casts = [
        'montant_payer' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => Auth::check() && $m->created_by = Auth::id());
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereHas('vente', fn ($q) => $q->visible());
    }

    public function vente()
    {
        return $this->belongsTo(InitVente::class, 'id_init_vente');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
