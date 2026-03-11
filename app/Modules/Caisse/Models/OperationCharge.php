<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;

use App\Modules\Caisse\Models\ChargeCategory;
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
     * VISIBILITÉ PAR STATION ACTIVE
     * ===================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $stationId = request()->attributes->get('station_active_id');

        return $stationId
            ? $query->where('id_station', $stationId)
            : $query->whereRaw('1=0');
    }

    /**
     * ===================================
     * RELATIONS
     * ===================================
     */

    /**
     * Station
     */
    public function station()
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    /**
     * Catégorie de charge
     */
    public function chargeCategory()
    {
        return $this->belongsTo(ChargeCategory::class, 'id_charge_category');
    }

    /**
     * Compte utilisé
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }

    /**
     * Audit
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