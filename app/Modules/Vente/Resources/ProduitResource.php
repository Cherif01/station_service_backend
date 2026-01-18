<?php

namespace App\Modules\Vente\Resources;

use App\Modules\Vente\Models\VenteLitre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * =================================================
         * 🔹 DERNIÈRE MESURE RÉELLE (JAUGEAGE)
         * =================================================
         */
        $derniereMesure = VenteLitre::visible()
            ->where('id_cuve', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $stockPhysiqueActuel = $derniereMesure
            ? (float) $derniereMesure->qte_vendu
            : 0;

        return [
            // =========================
            // IDENTITÉ CUVE
            // =========================
            'id'        => $this->id,
            'reference' => $this->reference,
            'libelle'   => $this->libelle,
            'status'    => (bool) $this->status,

            // =========================
            // DONNÉES DE STOCK (CUVE)
            // =========================
            'type'        => $this->type_cuve, // ex : gasoil, essence
            'qt_initial'  => (float) $this->qt_initial,

            /**
             * ⚠️ Valeur DB (informatif uniquement)
             */
            'qt_actuelle' => (float) $this->qt_actuelle,

            /**
             * ✅ VÉRITÉ MÉTIER (UTILISÉE PAR LE FRONT)
             */
            'stock_physique_actuel' => $stockPhysiqueActuel,
            'date_derniere_mesure'  => $derniereMesure
                ? $derniereMesure->created_at?->toDateTimeString()
                : null,

            // =========================
            // PRIX (VENTE)
            // =========================
            'pu_vente'    => (float) $this->pu_vente,
            'pu_unitaire' => (float) $this->pu_unitaire,

            // =========================
            // STATION (si chargée)
            // =========================
            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            // =========================
            // AUDIT
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
