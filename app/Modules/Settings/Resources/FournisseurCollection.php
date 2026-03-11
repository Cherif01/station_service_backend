<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FournisseurCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {

            return [

                'id' => $item->id,

                'raison_sociale' => $item->raison_sociale,

                'nom_complet' => $item->nom_complet,

                'telephone' => $item->telephone,

                'email' => $item->email,

                'status' => (bool) $item->status,

                'created_at' => $item->created_at?->toDateTimeString(),
            ];

        })->values()->toArray();
    }
}