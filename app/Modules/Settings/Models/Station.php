<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Station extends Model
{
    protected $table = 'stations';

    protected $fillable = [
        'libelle',
        'code',
        'adresse',
        'latitude',
        'longitude',
        'id_ville',
        'status',
        'created_by',
        'modify_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {

            // Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Génération automatique du code station
            if (empty($m->code)) {

                $lastId = self::max('id') + 1;

                $m->code = 'STAT-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * ============================
     * Relations
     * ============================
     */

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
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
