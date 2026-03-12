<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovisionnementCuveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'qte_appro'   => $this->qte_appro,
            'type'   => $this->type_appro,
            'pu_unitaire' => $this->pu_unitaire,
            'commentaire' => $this->commentaire,

            'cuve' => $this->whenLoaded('cuve', fn () => [
                'id'        => $this->cuve->id,
               
                'libelle'   => $this->cuve->libelle,
            ]),
              'fournisseur' => $this->whenLoaded('fournisseur', fn () => $this->fournisseur ? [
                'id'            => $this->fournisseur->id,
                'raison_sociale'=> $this->fournisseur->raison_sociale,
                'nom_complet'   => $this->fournisseur->nom_complet,
                'telephone'     => $this->fournisseur->telephone,
            ] : null),


            'created_by' => $this->createdBy?->name,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
