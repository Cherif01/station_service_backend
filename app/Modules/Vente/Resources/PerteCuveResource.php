<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerteCuveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'quantite_perdue' => (float) $this->quantite_perdue,
            'commentaire'     => $this->commentaire,

            'cuve' => $this->whenLoaded('cuve', fn () => [
                'id'      => $this->cuve->id,
                'libelle' => $this->cuve->libelle,
            ]),

            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
