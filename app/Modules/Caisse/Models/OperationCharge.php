<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OperationCharge extends Model
{
    protected $table = 'operation_charges';

    protected $fillable = [
        'id_station',
        'id_charge_category',
        'id_compte',
        'montant',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'status'  => 'boolean',
        'montant' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => Auth::check() && $m->created_by = Auth::id());
        static::updating(fn ($m) => Auth::check() && $m->modify_by = Auth::id());
    }

    /**
     * ===================================
     * VISIBILITÉ : accessible à tous les
     * utilisateurs authentifiés du tenant
     * (géré par le super_admin uniquement)
     * ===================================
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

    /**
     * ===================================
     * RELATIONS
     * ===================================
     */
    public function station()
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function chargeCategory()
    {
        return $this->belongsTo(ChargeCategory::class, 'id_charge_category');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'id_compte');
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
