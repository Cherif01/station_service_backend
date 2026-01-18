<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'libelle'       => $this->libelle,
            'qte_initiale'  => $this->qte_initiale,
            'qte_actuelle'  => $this->qte_actuelle,
            'prix_unitaire' => $this->prix_unitaire,
            'seuil_alerte'  => $this->seuil_alerte,
            'status'        => $this->status,

            'id_station' => $this->id_station,

            'created_by' => $this->created_by,
            'modify_by'  => $this->modify_by,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
