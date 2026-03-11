<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FournisseurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'raison_sociale' => $this->raison_sociale,

            'nom_complet' => $this->nom_complet,

            'telephone' => $this->telephone,

            'email' => $this->email,

            'adresse' => $this->adresse,

            'status' => (bool) $this->status,

            'created_by' => $this->createdBy?->name,

            'modify_by' => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),

            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}