<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'libelle'  => $this->libelle,
            'is_fixed' => (bool) $this->is_fixed,
            'status'   => (bool) $this->status,

            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'modify_by'  => $this->whenLoaded('modifiedBy', fn () => $this->modifiedBy?->name),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
