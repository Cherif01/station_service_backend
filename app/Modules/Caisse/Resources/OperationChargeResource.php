<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'categorie' => $this->whenLoaded('chargeCategory', fn () => $this->chargeCategory?->libelle),
            'compte'    => $this->whenLoaded('compte', fn () => $this->compte?->libelle),

            'montant'     => (float) $this->montant,
            'commentaire' => $this->commentaire,
            'status'      => (bool) $this->status,

            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'modify_by'  => $this->whenLoaded('modifiedBy', fn () => $this->modifiedBy?->name),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
