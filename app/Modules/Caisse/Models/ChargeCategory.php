<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChargeCategory extends Model
{
    protected $table = 'charge_categories';

    protected $fillable = [
        'libelle',
        'is_fixed',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_fixed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => Auth::check() && $m->created_by = Auth::id());
        static::updating(fn ($m) => Auth::check() && $m->modify_by = Auth::id());
    }

    /**
     * VISIBILITÉ
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}