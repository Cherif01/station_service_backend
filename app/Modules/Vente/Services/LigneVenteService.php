<?php
namespace App\Modules\Vente\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Settings\Services\PompeService;
use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\JaugeageCuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\PerteCuve;
use App\Modules\Vente\Models\ValidationVente;
use App\Modules\Vente\Resources\LigneVenteCollection;
use App\Modules\Vente\Resources\LigneVenteResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des lignes de vente.',
                'error'   => $e->getMessage(),
            ], 500);
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
                ->whereDate('created_at', today()) // seulement aujourd'hui
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => new LigneVenteCollection($items),
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
     * DÉTAIL D'UNE LIGNE
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
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Ligne de vente introuvable.',
            ], 404);
        }
    }

    /**
     * =========================
     * INITIALISATION DES LIGNES
     * =========================
     */
    // public function store(array $data): JsonResponse
    // {
    //     DB::beginTransaction();

    //     try {

    //         $stationId = $data['id_station'];

    //         /**
    //          * ===============================
    //          * récupérer pompes
    //          * ===============================
    //          */
    //         $pompes = Pompe::visible()
    //             ->where('id_station', $stationId)
    //             ->get();

    //         if ($pompes->isEmpty()) {

    //             DB::rollBack();

    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Aucune pompe trouvée pour cette station.',
    //             ], 404);
    //         }

    //         /**
    //          * ===============================
    //          * récupérer pompistes
    //          * ===============================
    //          */
    //         $pompistes = User::visible()
    //             ->where('role', 'pompiste')
    //             ->where('id_station', $stationId)
    //             ->get();

    //         if ($pompistes->isEmpty()) {

    //             DB::rollBack();

    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Aucun pompiste disponible.',
    //             ], 404);
    //         }

    //         $countPompistes = $pompistes->count();
    //         $i              = 0;

    //         foreach ($pompes as $pompe) {

    //             /**
    //              * ===============================
    //              * éviter doublon ligne vente
    //              * ===============================
    //              */
    //             $existe = LigneVente::visible()
    //                 ->whereDate('created_at', today())
    //                 ->whereHas('affectation', function ($q) use ($pompe) {
    //                     $q->where('id_pompe', $pompe->id);
    //                 })
    //                 ->exists();

    //             if ($existe) {
    //                 continue;
    //             }

    //             /**
    //              * ===============================
    //              * trouver cuve
    //              * ===============================
    //              */
    //             $cuve = Cuve::whereRaw('LOWER(libelle) = LOWER(?)', [$pompe->type_pompe])
    //                 ->first();

    //             if (! $cuve) {
    //                 continue;
    //             }

    //             /**
    //              * ===============================
    //              * dernier index pompe
    //              * ===============================
    //             */
    //             $indexData = $this->pompeService
    //                 ->getDernierIndexPourAffectation($pompe->id);

    //             $indexDebut = $indexData['index_debut'] ?? 0;

    //             /**
    //              * ===============================
    //              * choisir pompiste (rotation)
    //              * ===============================
    //              */
    //             $user = $pompistes[$i % $countPompistes];

    //             /**
    //              * ===============================
    //              * créer affectation
    //              * ===============================
    //              */
    //             $affectation = Affectation::create([
    //                 'id_user'    => $user->id,
    //                 'id_station' => $stationId,
    //                 'id_pompe'   => $pompe->id,
    //                 'status'     => true,
    //             ]);

    //             /**
    //              * ===============================
    //              * créer ligne vente
    //              * ===============================
    //              */
    //             LigneVente::create([
    //                 'id_station'     => $stationId,
    //                 'id_cuve'        => $cuve->id,
    //                 'id_affectation' => $affectation->id,
    //                 'index_debut'    => $indexDebut,
    //                 'index_fin'      => null,
    //                 'retour_cuve'    => 0,
    //                 'qte_vendu'      => 0,
    //                 'prix_unitaire'  => $cuve->pu_vente,
    //                 'status'         => false,
    //             ]);

    //             $i++;
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Initialisation des pompes effectuée.',
    //         ], 200);

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de l\'initialisation.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

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
             * récupérer pompes
             * ===============================
             */
            $pompes = Pompe::visible()
                ->where('id_station', $stationId)
                ->get();

            if ($pompes->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Aucune pompe trouvée pour cette station.',
                ], 404);
            }

            foreach ($pompes as $pompe) {

                /**
                 * ===============================
                 * éviter doublon ligne vente
                 * ===============================
                 */
                $existe = LigneVente::visible()
                    ->whereDate('created_at', today())
                    ->whereHas('affectation', function ($q) use ($pompe) {
                        $q->where('id_pompe', $pompe->id);
                    })
                    ->exists();

                if ($existe) {
                    continue;
                }

                /**
                 * ===============================
                 * trouver cuve
                 * ===============================
                 */
                $cuve = Cuve::whereRaw('LOWER(libelle) = LOWER(?)', [$pompe->type_pompe])
                    ->first();

                if (! $cuve) {
                    continue;
                }

                /**
                 * ===============================
                 * dernier index pompe
                 * ===============================
                 */
                $indexData = $this->pompeService
                    ->getDernierIndexPourAffectation($pompe->id);

                $indexDebut = $indexData['index_debut'] ?? 0;

                /**
                 * ===============================
                 * créer affectation sans pompiste
                 * (pompiste confirmé à la clôture)
                 * ===============================
                 */
                $affectation = Affectation::create([
                    'id_user'    => null,
                    'id_station' => $stationId,
                    'id_pompe'   => $pompe->id,
                    'status'     => true,
                ]);

                /**
                 * ===============================
                 * créer ligne vente
                 * ===============================
                 */
                LigneVente::create([
                    'id_station'     => $stationId,
                    'id_cuve'        => $cuve->id,
                    'id_affectation' => $affectation->id,
                    'index_debut'    => $indexDebut,
                    'index_fin'      => 0,
                    'retour_cuve'    => 0,
                    'qte_vendu'      => 0,
                    'prix_unitaire'  => $cuve->pu_vente,
                    'status'         => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Initialisation des pompes effectuée.',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l\'initialisation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * CLÔTURE VENTE
     * =========================
     */
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
                ], 404);
            }

            if ((bool) $item->status === true) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ], 409);
            }

            /**
             * ==========================================
             * INDEX
             * ==========================================
             */
            $indexDebut = (float) $item->index_debut;
            $indexFin   = $data['index_fin'] ?? null;

            if ($indexFin === null) {

                DB::rollBack();

                return response()->json([
                    'status'  => 400,
                    'message' => 'Index fin requis pour la validation.',
                ], 400);
            }

            $indexFin = (float) $indexFin;

            if ($indexFin < $indexDebut) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Index incohérent : index_fin < index_debut.',
                ], 409);
            }

            /**
             * ==========================================
             * RETOUR CUVE
             * ==========================================
             */
            $retourCuve = (float) ($data['retour_cuve'] ?? 0);

            if ($retourCuve < 0) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Retour cuve invalide.',
                ], 409);
            }

            /**
             * ==========================================
             * QUANTITÉ VENDUE
             * ==========================================
             */
            $qteVendu = round(($indexFin - $indexDebut) - $retourCuve, 3);

            if ($qteVendu <= 0) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ], 409);
            }

            /**
             * ==========================================
             * CUVE
             * ==========================================
             */
            $cuve = Cuve::lockForUpdate()->find($item->id_cuve);

            if (! $cuve) {

                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            $puVente = (float) ($cuve->pu_vente ?? 0);

            if ($puVente <= 0) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Prix unitaire invalide pour cette cuve.',
                ], 409);
            }

            /**
             * ==========================================
             * VÉRIFICATION STOCK DISPONIBLE
             * ==========================================
             */
            $dernierJaugeage = JaugeageCuve::where('id_cuve', $item->id_cuve)
                ->orderByDesc('created_at')
                ->first();

            if ($dernierJaugeage) {

                $stockRef    = (float) $dernierJaugeage->volume_mesure;
                $dateRef     = $dernierJaugeage->created_at;

                $totalAppros = ApprovisionnementCuve::where('id_cuve', $item->id_cuve)
                    ->where('type_appro', 'approvisionnement')
                    ->where('created_at', '>=', $dateRef)
                    ->sum('qte_appro');

                $totalVentes = LigneVente::where('id_cuve', $item->id_cuve)
                    ->where('status', true)
                    ->where('created_at', '>=', $dateRef)
                    ->sum('qte_vendu');

                $totalPertes = PerteCuve::where('id_cuve', $item->id_cuve)
                    ->where('created_at', '>=', $dateRef)
                    ->sum('quantite_perdue');

                $stockDispo = round($stockRef + $totalAppros - $totalVentes - $totalPertes, 3);

                if ($stockDispo < $qteVendu) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 409,
                        'message' => "Stock insuffisant. Disponible : {$stockDispo} L, demandé : {$qteVendu} L.",
                    ], 409);
                }
            }

            /**
             * ==========================================
             * UPDATE VENTE
             * ==========================================
             */
            $status = $data['status'] ?? true;

            $item->update([
                'index_fin'     => $indexFin,
                'retour_cuve'   => $retourCuve,
                'qte_vendu'     => $qteVendu,
                'prix_unitaire' => $puVente,
                'status'        => $status,
            ]);

            /**
             * ==========================================
             * MONTANT
             * ==========================================
             */
            $montant = $qteVendu * $puVente;

            $commentaireAuto =
                "Vente validée\n" .
                "Volume : {$qteVendu} L\n" .
                "Retour cuve : {$retourCuve} L\n" .
                "PU : {$puVente} GNF\n" .
                "Montant : {$montant} GNF\n" .
                "Cuve : {$cuve->libelle}";

            ValidationVente::create([
                'id_vente'    => $item->id,
                'commentaire' => $commentaireAuto,
            ]);

            /**
             * ==========================================
             * FERMETURE AFFECTATION + CONFIRMATION POMPISTE
             * ==========================================
             */
            if (empty($data['id_pompiste'])) {

                DB::rollBack();

                return response()->json([
                    'status'  => 422,
                    'message' => 'Le pompiste est obligatoire pour clôturer la vente.',
                ], 422);
            }

            if ($item->id_affectation) {

                $affectation = Affectation::where('id', $item->id_affectation)
                    ->where('status', true)
                    ->lockForUpdate()
                    ->first();

                if ($affectation) {
                    $affectation->update([
                        'id_user' => $data['id_pompiste'],
                        'status'  => false,
                    ]);
                }
            }

            /**
             * ==========================================
             * COMPTABILITÉ
             * ==========================================
             */
            $compte = Compte::where('id_station', $item->id_station)
                ->lockForUpdate()
                ->first();

            if (! $compte) {

                DB::rollBack();

                return response()->json([
                    'status'  => 500,
                    'message' => 'Compte de la station introuvable.',
                ], 500);
            }

            OperationCompte::create([
                'id_compte'         => $compte->id,
                'id_type_operation' => 1,
                'montant'           => $montant,
                'libelle'           => 'Vente carburant - ' . $cuve->libelle,
                'reference'         => 'VENTE-' . $item->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente clôturée avec succès.',
                'data'    => new LigneVenteResource($item->fresh()),
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la clôture.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION / ANNULATION
     * =========================
     */
    public function delete(int $id, array $data = []): JsonResponse
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
                ], 404);
            }

            // Vente non encore validée : suppression simple, tout le monde peut le faire
            if ((bool) $item->status === false) {

                $item->delete();

                DB::commit();

                return response()->json([
                    'status'  => 200,
                    'message' => 'Vente non validée supprimée avec succès.',
                ], 200);
            }

            // Vente validée : réservé aux admin / super_admin
            $user = Auth::user();

            if (! in_array($user->role, ['admin', 'super_admin'])) {

                DB::rollBack();

                return response()->json([
                    'status'  => 403,
                    'message' => 'Seul un administrateur peut annuler une vente validée.',
                ], 403);
            }

            $validation = ValidationVente::where('id_vente', $item->id)
                ->lockForUpdate()
                ->first();

            if (! $validation) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Validation de vente introuvable.',
                ], 409);
            }

            $operationVente = OperationCompte::where('reference', 'VENTE-' . $item->id)
                ->lockForUpdate()
                ->first();

            if (! $operationVente) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Opération comptable de la vente introuvable.',
                ], 409);
            }

            $typeAnnulation = TypeOperation::where('nature', 0)->first();

            if (! $typeAnnulation) {

                DB::rollBack();

                return response()->json([
                    'status'  => 500,
                    'message' => "Type operation Sortie non configure. Contactez l'administrateur.",
                ], 500);
            }

            OperationCompte::create([
                'id_compte'         => $operationVente->id_compte,
                'id_type_operation' => $typeAnnulation->id,
                'montant'           => $operationVente->montant,
                'reference'         => 'ANNUL-VENTE-' . $item->id . '-' . now()->timestamp,
                'commentaire'       => 'Annulation vente carburant',
            ]);

            $item->update([
                'status'      => false,
                'index_fin'   => 0,
                'retour_cuve' => 0,
                'qte_vendu'   => 0,
            ]);

            $raison    = ! empty($data['raison']) ? ' - Raison : ' . $data['raison'] : '';
            $annulePar = $user->name ?? 'Admin';

            $validation->update([
                'commentaire' => $validation->commentaire
                    . "\n---\nVENTE ANNULÉE LE " . now()->format('d/m/Y H:i')
                    . ' par ' . $annulePar
                    . $raison,
            ]);

            // Rouvrir l'affectation pour permettre une re-saisie
            $affectation = Affectation::lockForUpdate()->find($item->id_affectation);

            if ($affectation) {
                $affectation->update([
                    'status'  => true,
                    'id_user' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente annulée avec succès.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => "Erreur lors de l'annulation de la vente.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * VENTE JOURNALIÈRE PAR POMPE
     * (relevé jour par jour entre deux dates)
     * =========================
     */
    public function venteJournaliere(?string $dateDebut = null, ?string $dateFin = null): JsonResponse
    {
        try {

            $debut = $dateDebut
                ? Carbon::parse($dateDebut)->startOfDay()
                : Carbon::today()->startOfDay();

            $fin = $dateFin
                ? Carbon::parse($dateFin)->endOfDay()
                : Carbon::today()->endOfDay();

            $data    = [];
            $current = $debut->copy();

            while ($current->lte($fin)) {

                $date = $current->toDateString();

                $lignes = LigneVente::visible()
                    ->with([
                        'affectation.pompe.station',
                        'affectation.user',
                        'cuve',
                        'createdBy',
                        'modifiedBy',
                    ])
                    ->whereDate('created_at', $date)
                    ->where('status', true)
                    ->get();

                $data[] = [
                    'date'          => $date,
                    'total_volume'  => (float) $lignes->sum('qte_vendu'),
                    'total_montant' => (float) $lignes->sum(fn($l) => $l->qte_vendu * $l->prix_unitaire),
                    'lignes'        => (new LigneVenteCollection($lignes))->toArray(request()),
                ];

                $current->addDay();
            }

            return response()->json([
                'status'     => 200,
                'date_debut' => $debut->toDateString(),
                'date_fin'   => $fin->toDateString(),
                'data'       => $data,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du relevé journalier.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
