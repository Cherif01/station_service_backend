<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'nom_complet' => $this->nom_complet,
            'telephone'   => $this->telephone,
            'email'       => $this->email,
            'adresse'     => $this->adresse,
            'status'      => $this->status,

            // station (flat)
            'id_station'  => $this->id_station,
            'station'     => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            // ventes
            'ventes' => InitVenteResource::collection($this->whenLoaded('ventes')),

            // audit
            'created_by' => $this->created_by,
            'modify_by'  => $this->modify_by,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
