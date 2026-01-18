<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'id_station',
        'libelle',
        'prix',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'prix'   => 'float',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => Auth::check() && $m->created_by = Auth::id());
        static::updating(fn ($m) => Auth::check() && $m->modify_by = Auth::id());
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('id_station', request()->attributes->get('station_active_id'));
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'id_station');
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
