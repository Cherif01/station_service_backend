<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OperationChargeCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {

            return [

                'id'      => $item->id,
                'station' => $item->station?->libelle,

                'categorie' => $item->chargeCategory?->libelle,

                'compte' => $item->compte?->libelle,

                'montant' => (float) $item->montant,

                'status' => (bool) $item->status,

                'created_at' => $item->created_at?->toDateTimeString(),
            ];

        })->values()->toArray();
    }
}