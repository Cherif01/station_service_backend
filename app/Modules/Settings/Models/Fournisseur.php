<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Fournisseur extends Model
{
    protected $table = 'fournisseurs';

    protected $fillable = [
        'raison_sociale',
        'nom_complet',
        'telephone',
        'email',
        'adresse',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => Auth::check() && $m->created_by = Auth::id());

        static::updating(fn ($m) => Auth::check() && $m->modify_by = Auth::id());
    }

    /**
     * RELATIONS AUDIT
     */

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}