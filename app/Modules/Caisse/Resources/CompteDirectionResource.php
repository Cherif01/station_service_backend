<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteDirectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'numero'        => $this->numero,
            'libelle'       => $this->libelle,
            'commentaire'   => $this->commentaire,
            'solde_initial' => (float) $this->solde_initial,
            'solde_actuel'  => (float) $this->solde_actuel,

            'created_by'    => $this->createdBy?->name,
            'modify_by'     => $this->modifiedBy?->name,
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
        ];
    }
}
