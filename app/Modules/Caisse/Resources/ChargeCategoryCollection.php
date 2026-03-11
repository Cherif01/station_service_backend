<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ChargeCategoryCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {

            return [

                'id' => $item->id,

                'libelle' => $item->libelle,

                'is_fixed' => (bool) $item->is_fixed,

                'status' => (bool) $item->status,
            ];
        })->values()->toArray();
    }
}