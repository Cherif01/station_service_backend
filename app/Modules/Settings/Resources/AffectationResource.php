<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffectationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,

            'pompe' => [
                'id'        => $this->pompe?->id,
                'libelle'   => $this->pompe?->libelle,
                'reference' => $this->pompe?->reference,
            ],

            'pompiste' => [
                'id'   => $this->pompiste?->id,
                'name' => $this->pompiste?->name,
            ],

            'station' => [
                'id'      => $this->station?->id,
                'libelle' => $this->station?->libelle,
                'code'    => $this->station?->code,
            ],

            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
