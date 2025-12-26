<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PompeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'libelle'       => $this->libelle,
            'reference'     => $this->reference,
            'type_pompe'    => $this->type_pompe,
            'index_initial' => $this->index_initial,
            'status'        => $this->status,

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
