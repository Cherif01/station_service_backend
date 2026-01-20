<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'id_station',
        'reference',
        'libelle',
        'qte_initiale',
        'qte_actuelle',
        'prix_unitaire',
        'seuil_alerte',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'qte_initiale'  => 'float',
        'qte_actuelle'  => 'float',
        'prix_unitaire' => 'float',
        'status'        => 'boolean',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + RÉFÉRENCE LIÉE À LA STATION
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            // 🔐 Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            /**
             * =============================================
             * 🔹 STATION ACTIVE (OBLIGATOIRE)
             * =============================================
             */
            if (empty($m->id_station)) {
                $stationId = request()->attributes->get('station_active_id');

                if (! $stationId) {
                    throw new \Exception('Aucune station active détectée pour la création du produit.');
                }

                $m->id_station = $stationId;
            }

            /**
             * =============================================
             * 🔹 GÉNÉRATION RÉFÉRENCE (LIÉE À LA STATION)
             * Format : ST{station}-PRD-YYYYMMDD-XXXX
             * =============================================
             */
            if (empty($m->reference)) {

                $date = now()->format('Ymd');

                // Compteur par station et par jour
                $count = self::where('id_station', $m->id_station)
                    ->whereDate('created_at', now()->toDateString())
                    ->count() + 1;

                $m->reference = sprintf(
                    'ST%d-PRD-%s-%04d',
                    $m->id_station,
                    $date,
                    $count
                );
            }
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPES
     * =================================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(
            'id_station',
            request()->attributes->get('station_active_id')
        );
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
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
