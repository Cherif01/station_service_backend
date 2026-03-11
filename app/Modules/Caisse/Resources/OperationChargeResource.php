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

            'station' => $this->station?->libelle,

            'categorie' => $this->chargeCategory?->libelle,

            'compte' => $this->compte?->libelle,

            'montant' => (float) $this->montant,

            'commentaire' => $this->commentaire,

            'status' => (bool) $this->status,

            'created_by' => $this->createdBy?->name,

            'modify_by' => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),

            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}