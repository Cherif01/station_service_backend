<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovisionnementProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'qte_appro'   => $this->qte_appro,
            'type'   => $this->type_appro,
            'pu_unitaire' => $this->pu_unitaire,
            'commentaire' => $this->commentaire,

            'produit' => $this->whenLoaded('produit', fn () => [
                'id'        => $this->cuve->id,
                'reference' => $this->cuve->reference,
                'libelle'   => $this->cuve->libelle,
            ]),

            'created_by' => $this->createdBy?->name,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
