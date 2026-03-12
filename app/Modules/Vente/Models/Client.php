<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'id_station',
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
     * VISIBILITÉ PAR STATION ACTIVE
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
        return $query->where('id_station', $stationActiveId);
    }

    return $query->whereRaw('1 = 0');
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
