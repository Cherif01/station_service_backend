<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JaugeageCuveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'hauteur'       => (float) $this->hauteur,
            'volume_mesure' => (float) $this->volume_mesure,
            'commentaire'   => $this->commentaire,
            'status'        => (bool) $this->status,

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
