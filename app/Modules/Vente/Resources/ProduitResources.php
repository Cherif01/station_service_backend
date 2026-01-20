<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // =============================
            // 🔹 IDENTITÉ
            // =============================
            'id'        => $this->id,
            'reference' => $this->reference,
            'libelle'   => $this->libelle,
            'status'    => (bool) $this->status,

            // =============================
            // 🔹 STOCK
            // =============================
            'qte_initiale' => (float) $this->qte_initiale,
            'qte_actuelle' => (float) $this->qte_actuelle,
            'seuil_alerte' => (float) $this->seuil_alerte,

            // =============================
            // 🔹 PRIX
            // =============================
            'prix_unitaire' => (float) $this->prix_unitaire,

            // =============================
            // 🔹 STATION
            // =============================
            'id_station' => $this->id_station,

            // =============================
            // 🔹 AUDIT (NOMS LISIBLES)
            // =============================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            // =============================
            // 🔹 DATES
            // =============================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
