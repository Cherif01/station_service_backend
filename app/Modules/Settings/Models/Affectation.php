<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    protected $table = 'affectations';

    protected $fillable = [
        'id_pompe',
        'id_pompiste',
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
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    public function pompe(): BelongsTo
    {
        return $this->belongsTo(Pompe::class, 'id_pompe');
    }

    public function pompiste(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pompiste');
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
