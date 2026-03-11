<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JaugeageCuveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'id_cuve'       => $this->id_cuve,

            'hauteur'       => (float) $this->hauteur,
            'volume_mesure' => (float) $this->volume_mesure,

            'commentaire'   => $this->commentaire,
            'status'        => (bool) $this->status,

            /**
             * =========================
             * CUVE
             * =========================
             */
            'cuve' => $this->whenLoaded('cuve', function () {

                return [
                    'id'           => $this->cuve->id,
                    'libelle'      => $this->cuve->libelle,
                    'reference'    => $this->cuve->reference,
                    'qt_actuelle'  => (float) $this->cuve->qt_actuelle,
                ];

            }),

            /**
             * =========================
             * AUDIT
             * =========================
             */
            'created_by' => $this->created_by,
            'modify_by'  => $this->modify_by,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}