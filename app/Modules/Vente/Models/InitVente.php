<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InitVente extends Model
{
    protected $table = 'init_ventes';

    protected $fillable = [
        'id_station',
        'id_client',
        'id_affectation',
        'reference',
        'status',
        'created_by',
        'modify_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (Auth::check()) $m->created_by = Auth::id();
            if (! $m->reference) {
                $m->reference = 'VNT-' . strtoupper(Str::random(8));
            }
        });

        static::updating(fn ($m) => Auth::check() && $m->modify_by = Auth::id());
    }

    public function scopeVisible(Builder $query): Builder
    {
        $stationActiveId = request()->attributes->get('station_active_id');

        if (! $stationActiveId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('client', function (Builder $q) use ($stationActiveId) {
            $q->where('id_station', $stationActiveId);
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function lignes()
    {
        return $this->hasMany(VenteProduitService::class, 'id_init_vente');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_init_vente');
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
