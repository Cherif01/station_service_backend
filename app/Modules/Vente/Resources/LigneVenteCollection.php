<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LigneVenteCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($item) {

            return [

                'id' => $item->id,

                'index_debut' => (float) $item->index_debut,
                'index_fin'   => (float) $item->index_fin,
                'qte_vendu'   => (float) $item->qte_vendu,
                'retour_cuve' => (float) $item->retour_cuve,

                'status' => $item->status ? 'validée' : 'en cours',

                'station' => $item->affectation?->pompe?->station
                    ? [
                        'id'      => $item->affectation->pompe->station->id,
                        'libelle' => $item->affectation->pompe->station->libelle,
                    ]
                    : null,

                'pompe' => $item->affectation?->pompe
                    ? [
                        'id'        => $item->affectation->pompe->id,
                        'libelle'   => $item->affectation->pompe->libelle,
                        'reference' => $item->affectation->pompe->reference,
                    ]
                    : null,

                'created_by' => $item->createdBy?->name,
                'modify_by'  => $item->modifiedBy?->name,

                'created_at' => $item->created_at?->toDateTimeString(),
                'updated_at' => $item->updated_at?->toDateTimeString(),

            ];

        })->values()->toArray();
    }
}