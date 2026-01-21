<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =============================
            // 🔹 IDENTITÉ
            // =============================
            'id'  => $this->id,
            'cle' => $this->cle,

            // =============================
            // 🔹 VALEUR
            // =============================
            'valeur'      => $this->valeur,
            'description' => $this->description,

            // =============================
            // 🔹 PORTÉE
            // =============================
            'id_station' => $this->id_station,

            // =============================
            // 🔹 AUDIT
            // =============================
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,

            // =============================
            // 🔹 DATES
            // =============================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
