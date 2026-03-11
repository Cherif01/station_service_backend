<?php

namespace App\Modules\Vente\Resources;

use App\Modules\Vente\Models\JaugeageCuve;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CuveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * =================================================
         * 🔹 DERNIÈRE MESURE RÉELLE (JAUGEAGE)
         * =================================================
         */
        $derniereMesure = JaugeageCuve::visible()
            ->where('id_cuve', $this->id)
            ->orderByDesc('created_at')
            ->first();

        $stockPhysiqueActuel = $derniereMesure
            ? (float) $derniereMesure->volume_mesure
            : 0;

        return [

            /**
             * =========================
             * IDENTITÉ CUVE
             * =========================
             */
            'id'        => $this->id,
            'reference' => $this->reference,
            'libelle'   => $this->libelle,
            'status'    => (bool) $this->status,

            /**
             * =========================
             * DONNÉES STOCK
             * =========================
             */
            'type'       => $this->type_cuve,
            'qt_initial' => (float) $this->qt_initial,

            /**
             * ⚠️ Valeur stock DB (informatif)
             */
            'qt_actuelle' => (float) $this->qt_actuelle,

            /**
             * ✅ VÉRITÉ MÉTIER
             */
            'stock_physique_actuel' => $stockPhysiqueActuel,

            'date_derniere_mesure' => $derniereMesure
                ? $derniereMesure->created_at?->toDateTimeString()
                : null,

            /**
             * =========================
             * PRIX
             * =========================
             */
            'pu_vente'    => (float) $this->pu_vente,
            'pu_unitaire' => (float) $this->pu_unitaire,

            /**
             * =========================
             * STATION
             * =========================
             */
            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            /**
             * =========================
             * AUDIT
             * =========================
             */
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}