<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersementDirectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // IDENTITÉ
            // =========================
            'id'          => $this->id,
            'reference'   => $this->reference,
            'montant'     => (float) $this->montant,
            'mode'        => $this->mode,
            'status'      => $this->status,
            'commentaire' => $this->commentaire,

            // =========================
            // COMPTE SOURCE (station)
            // =========================
            'source' => $this->source ? [
                'id'      => $this->source->id,
                'numero'  => $this->source->numero,
                'libelle' => $this->source->libelle,
                'station' => $this->source->station ? [
                    'id'      => $this->source->station->id,
                    'libelle' => $this->source->station->libelle,
                ] : null,
            ] : null,

            // =========================
            // COMPTE DIRECTION (destination)
            // =========================
            'compte_direction' => $this->compteDirection ? [
                'id'      => $this->compteDirection->id,
                'numero'  => $this->compteDirection->numero,
                'libelle' => $this->compteDirection->libelle,
            ] : null,

            // =========================
            // TYPE OPÉRATION
            // =========================
            'type_operation' => $this->typeOperation ? [
                'id'      => $this->typeOperation->id,
                'libelle' => $this->typeOperation->libelle,
                'nature'  => $this->typeOperation->nature,
            ] : null,

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
