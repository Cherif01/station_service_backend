<?php

namespace App\Modules\Caisse\Resources;

use App\Modules\Vente\Models\Creance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'libelle'     => $this->libelle,
            'numero'      => $this->numero,
            'commentaire' => $this->commentaire,

            // =============================================
            // 🔹 STATION + DERNIER GÉRANT
            // =============================================
            'station' => $this->whenLoaded('station', function () {

                $gerant = null;

                if ($this->station->relationLoaded('affectations')) {
                    $gerant = $this->station->affectations
                        ->filter(fn ($a) => $a->user && $a->user->role === 'gerant')
                        ->sortByDesc('created_at')
                        ->first()?->user;
                }

                return [
                    'id'      => $this->station->id,
                    'libelle' => $this->station->libelle,

                    'dernier_gerant' => $gerant ? [
                        'name'      => $gerant->name,
                        'email'     => $gerant->email,
                        'telephone' => $gerant->telephone,
                        'adresse'   => $gerant->adresse,
                    ] : null,
                ];
            }),

            // =============================================
            // 🔹 SOLDES
            // =============================================
            'solde_initial'  => (float) $this->solde_initial,
            'total_creances' => (float) Creance::where('id_station', $this->id_station)->sum('montant'),
            'solde_actuel'   => (float) $this->solde_actuel,

            // =============================================
            // 🔹 AUDIT
            // =============================================
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'modify_by'  => $this->whenLoaded('modifiedBy', fn () => $this->modifiedBy?->name),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
