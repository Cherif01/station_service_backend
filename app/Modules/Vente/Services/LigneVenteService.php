<?php

namespace App\Modules\Vente\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;

use App\Modules\Vente\Models\ValidationVente;
use App\Modules\Vente\Resources\LigneVenteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Modules\Settings\Services\PompeService;

class LigneVenteService
{
    protected $pompeService;

    public function __construct(PompeService $pompeService)
    {
        $this->pompeService = $pompeService;
    }

    /**
     * =========================
     * LISTE DES LIGNES DE VENTE
     * =========================
     */
    public function getAll(): JsonResponse
    {
        try {

            $items = LigneVente::visible()
                ->with([
                    'station',
                    'cuve',
                    'affectation.pompe.station',
                    'affectation.user',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => LigneVenteResource::collection($items),
            ],200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des lignes de vente.',
                'error'   => $e->getMessage(),
            ],500);
        }
    }

    public function getAll1(): JsonResponse
{
    try {

        $items = LigneVente::visible()
            ->with([
                'station',
                'cuve',
                'affectation.pompe.station',
                'affectation.user',
                'createdBy',
                'modifiedBy',
            ])
            ->whereDate('created_at', today()) // 🔹 seulement aujourd'hui
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => LigneVenteResource::collection($items),
        ], 200);

    } catch (Throwable $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des lignes de vente.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * =========================
     * DÉTAIL D’UNE LIGNE
     * =========================
     */
    public function getOne(int $id): JsonResponse
    {
        try {

            $item = LigneVente::visible()
                ->with([
                    'station',
                    'cuve',
                    'affectation.pompe.station',
                    'affectation.user',
                    'createdBy',
                    'modifiedBy',
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new LigneVenteResource($item),
            ],200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Ligne de vente introuvable.',
            ],404);
        }
    }

    /**
     * =========================
     * INITIALISATION DES LIGNES
     * =========================
     */
    public function store(array $data): JsonResponse
    {
        DB::beginTransaction();

        try {

            $stationId = $data['id_station'];

            /**
             * ===============================
             * 1. récupérer pompes station
             * ===============================
             */
            $pompes = Pompe::where('id_station', $stationId)->get();

            if ($pompes->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'status' => 404,
                    'message' => 'Aucune pompe trouvée pour cette station.'
                ],404);
            }

            /**
             * ===============================
             * 2. récupérer pompistes
             * ===============================
             */
            $pompistes = User::where('role','pompiste')
                ->where('id_station',$stationId)
                ->get();

            if ($pompistes->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'status' => 404,
                    'message' => 'Aucun pompiste disponible.'
                ],404);
            }

            $i = 0;

            foreach ($pompes as $pompe) {

                if (!isset($pompistes[$i])) {
                    break;
                }

                $user = $pompistes[$i];

                /**
                 * ===============================
                 * 3. trouver cuve
                 * ===============================
                 */
                $cuve = Cuve::where('libelle',$pompe->type_pompe)->first();

                if (!$cuve) {
                    $i++;
                    continue;
                }

                /**
                 * ===============================
                 * 4. récupérer dernier index
                 * ===============================
                 */
                $indexData = $this->pompeService->getDernierIndexPourAffectation($pompe->id);

                $indexDebut = $indexData['index_debut'] ?? 0;

                /**
                 * ===============================
                 * 5. créer affectation
                 * ===============================
                 */
                $affectation = Affectation::create([
                    'id_user'    => $user->id,
                    'id_station' => $stationId,
                    'id_pompe'   => $pompe->id,
                    
                    'status'     => true
                ]);

                /**
                 * ===============================
                 * 6. créer ligne vente
                 * ===============================
                 */
                LigneVente::create([
                    'id_station'      => $stationId,
                    'id_cuve'         => $cuve->id,
                    'id_affectation'  => $affectation->id,
                    'index_debut'     => $indexDebut,
                    'index_precedent' => $indexDebut,
                    'status'          => false
                ]);

                $i++;
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Initialisation des pompes et pompistes effectuée.'
            ],200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de l’initialisation.',
                'error'   => $e->getMessage()
            ],500);
        }
    }

    /**
     * =========================
     * CLÔTURE VENTE
     * =========================
     */
    public function update(int $id, array $data): JsonResponse
    {
        DB::beginTransaction();

        try {

            $item = LigneVente::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $item) {

                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ],404);
            }

            if ((bool) $item->status === true) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ],409);
            }

            $indexDebut = (float) $item->index_debut;
            $indexFin   = $data['index_fin'] ?? null;

            if ($indexFin === null) {

                DB::rollBack();

                return response()->json([
                    'status'  => 400,
                    'message' => 'Index fin requis pour la validation.',
                ],400);
            }

            $indexFin = (float) $indexFin;

            if ($indexFin < $indexDebut) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Index incohérent : index_fin < index_debut.',
                ],409);
            }

            $qteVendu = $indexFin - $indexDebut;

            if ($qteVendu <= 0) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ],409);
            }

            $cuve = Cuve::lockForUpdate()->find($item->id_cuve);

            if (! $cuve) {

                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ],404);
            }

            $puVente = (float) $cuve->pu_vente;

            if ($puVente <= 0) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Prix unitaire invalide pour cette cuve.',
                ],409);
            }

            $item->update([
                'index_fin'     => $indexFin,
                'qte_vendu'     => $qteVendu,
                'prix_unitaire' => $puVente,
                'status'        => true,
            ]);

            $montant = $qteVendu * $puVente;

            $commentaireAuto =
                "Vente validée\n".
                "Volume : {$qteVendu} L\n".
                "PU : {$puVente} GNF\n".
                "Montant : {$montant} GNF\n".
                "Cuve : {$cuve->libelle}";

            ValidationVente::create([
                'id_vente'    => $item->id,
                'commentaire' => $commentaireAuto,
            ]);

            if ($item->id_affectation) {

                $affectation = Affectation::where('id',$item->id_affectation)
                    ->where('status',true)
                    ->lockForUpdate()
                    ->first();

                if ($affectation) {

                    $affectation->update([
                        'status' => false
                    ]);
                }
            }

            $compte = Compte::where('id_station',$item->id_station)
                ->lockForUpdate()
                ->first();

            if (! $compte) {

                DB::rollBack();

                return response()->json([
                    'status'  => 500,
                    'message' => 'Compte de la station introuvable.',
                ],500);
            }

            OperationCompte::create([
                'id_compte'         => $compte->id,
                'id_type_operation' => 1,
                'montant'           => $montant,
                'libelle'           => 'Vente carburant - '.$cuve->libelle,
                'reference'         => 'VENTE-'.$item->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente clôturée, validée et comptabilisée avec succès.',
                'data'    => new LigneVenteResource($item->fresh()),
            ],200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la clôture de la vente.',
                'error'   => $e->getMessage(),
            ],500);
        }
    }

    /**
     * =========================
     * SUPPRESSION / ANNULATION
     * =========================
     */
    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {

            $item = LigneVente::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $item) {

                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ],404);
            }

            if ((bool) $item->status === false) {

                $item->delete();

                DB::commit();

                return response()->json([
                    'status'  => 200,
                    'message' => 'Vente non validée supprimée avec succès.',
                ],200);
            }

            $validation = ValidationVente::where('id_vente', $item->id)
                ->lockForUpdate()
                ->first();

            if (! $validation) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Validation de vente introuvable.',
                ],409);
            }

            $operationVente = OperationCompte::where('reference','VENTE-'.$item->id)
                ->lockForUpdate()
                ->first();

            if (! $operationVente) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Opération comptable de la vente introuvable.',
                ],409);
            }

            OperationCompte::create([
                'id_compte'         => $operationVente->id_compte,
                'id_type_operation' => 0,
                'montant'           => $operationVente->montant,
                'libelle'           => 'Annulation vente carburant',
                'reference'         => 'ANNUL-VENTE-'.$item->id,
            ]);

            $item->update([
                'status' => false,
            ]);

            $validation->update([
                'commentaire' => $validation->commentaire
                    . "\n---\nVENTE ANNULÉE LE ".now()->format('d/m/Y H:i'),
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente validée annulée avec succès.',
            ],200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’annulation de la vente.',
                'error'   => $e->getMessage(),
            ],500);
        }
    }
}