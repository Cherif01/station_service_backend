<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected static function booted(): void
    {
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // Génération auto référence si null
            if (empty($m->reference)) {
                $nextId = self::max('id') + 1;
                $m->reference = 'PMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

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
