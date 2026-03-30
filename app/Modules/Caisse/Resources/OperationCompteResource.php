<?php

namespace App\Modules\Caisse\Resources;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Vente\Models\Creance;
use App\Modules\Vente\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationCompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof OperationCompte) {
            return [
                'id'           => $this->id,
                'reference'    => $this->reference,
                'montant'      => (float) $this->montant,
                'status'       => $this->status,
                'commentaire'  => $this->commentaire,

                'type_operation' => $this->whenLoaded('typeOperation', function () {
                    return [
                        'id'      => $this->typeOperation->id,
                        'libelle' => $this->typeOperation->libelle,
                        'nature'  => (int) $this->typeOperation->nature,
                    ];
                }),

                'compte' => $this->whenLoaded('compte', function () {
                    return [
                        'id'      => $this->compte->id,
                        'libelle' => $this->compte->libelle,
                        'station' => $this->compte->station
                            ? [
                                'id'      => $this->compte->station->id,
                                'libelle' => $this->compte->station->libelle,
                            ]
                            : null,
                    ];
                }),

                'source' => $this->whenLoaded('source', function () {
                    return [
                        'id'      => $this->source->id,
                        'libelle' => $this->source->libelle,
                        'station' => $this->source->station
                            ? [
                                'id'      => $this->source->station->id,
                                'libelle' => $this->source->station->libelle,
                            ]
                            : null,
                    ];
                }),

                'destination' => $this->whenLoaded('destination', function () {
                    return [
                        'id'      => $this->destination->id,
                        'libelle' => $this->destination->libelle,
                        'station' => $this->destination->station
                            ? [
                                'id'      => $this->destination->station->id,
                                'libelle' => $this->destination->station->libelle,
                            ]
                            : null,
                    ];
                }),

                'created_by' => $this->createdBy?->name,
                'modify_by'  => $this->modifiedBy?->name,

                'created_at' => $this->created_at?->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ];
        }

        if ($this->resource instanceof Paiement) {
            return [
                'id'           => $this->id,
                'reference'    => 'PAIEMENT-CREANCE-' . $this->id,
                'montant'      => (float) $this->montant_payer,
                'status'       => true,
                'commentaire'  => 'Paiement de créance' . (
                    $this->relationLoaded('creance') &&
                    $this->creance &&
                    $this->creance->relationLoaded('client') &&
                    $this->creance->client
                        ? ' - ' . $this->creance->client->nom_complet
                        : ''
                ),

                'type_operation' => [
                    'id'      => 0,
                    'libelle' => 'Paiement créance',
                    'nature'  => 1,
                ],

                'compte' => $this->whenLoaded('compte', function () {
                    return [
                        'id'      => $this->compte->id,
                        'libelle' => $this->compte->libelle,
                        'station' => $this->compte->station
                            ? [
                                'id'      => $this->compte->station->id,
                                'libelle' => $this->compte->station->libelle,
                            ]
                            : null,
                    ];
                }),

                'source' => null,

                'destination' => null,

                'created_by' => $this->createdBy?->name,
                'modify_by'  => $this->modifiedBy?->name,

                'created_at' => $this->created_at?->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ];
        }

        if ($this->resource instanceof Creance) {

            $compte = Compte::where('id_station', $this->id_station)->with('station')->first();

            return [
                'id'          => $this->id,
                'reference'   => 'CREANCE-' . $this->id,
                'montant'     => (float) $this->montant,
                'status'      => true,
                'commentaire' => $this->commentaire ?? (
                    $this->relationLoaded('client') && $this->client
                        ? 'Creance - ' . $this->client->nom_complet
                        : 'Creance'
                ),

                'type_operation' => [
                    'id'      => 0,
                    'libelle' => 'Creance',
                    'nature'  => 0,
                ],

                'compte' => $compte ? [
                    'id'      => $compte->id,
                    'libelle' => $compte->libelle,
                    'station' => $compte->station ? [
                        'id'      => $compte->station->id,
                        'libelle' => $compte->station->libelle,
                    ] : null,
                ] : null,

                'source'      => null,
                'destination' => null,

                'created_by' => $this->createdBy?->name,
                'modify_by'  => null,

                'created_at' => $this->created_at?->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ];
        }

        return [];
    }
}