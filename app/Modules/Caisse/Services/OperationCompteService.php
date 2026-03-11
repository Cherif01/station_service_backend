<?php
namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCharge;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Caisse\Resources\OperationCompteResource;
use App\Modules\Caisse\Resources\OperationTransfertResource;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;
use Throwable;

class OperationCompteService
{


public function resumeMensuel(int $annee)
{
    try {

        $data = [];

        $user = auth()->user();

        for ($mois = 1; $mois <= 12; $mois++) {

            $start = Carbon::create($annee, $mois, 1)->startOfMonth();
            $end   = Carbon::create($annee, $mois, 1)->endOfMonth();

            /**
             * =========================
             * VENTES DIRECTES
             * =========================
             */
            $ventesQuery = LigneVente::query()
                ->where('status', true)
                ->whereBetween('created_at', [$start, $end]);

            /**
             * =========================
             * PAIEMENTS CREANCES
             * =========================
             */
            $creancesQuery = Paiement::query()
                ->whereBetween('created_at', [$start, $end]);

            /**
             * =========================
             * CHARGES
             * =========================
             */
            $chargesQuery = OperationCharge::query()
                ->whereBetween('created_at', [$start, $end]);

            /**
             * =========================
             * FILTRAGE STATION
             * =========================
             */
            if ($user->role !== 'super_admin') {

                $stationId = request()->attributes->get('station_active_id');

                $ventesQuery->where('id_station', $stationId);
                $creancesQuery->where('id_station', $stationId);
                $chargesQuery->where('id_station', $stationId);
            }

            $ventesDirectes = $ventesQuery->sum('montant');

            $paiementsCreances = $creancesQuery->sum('montant_payer');

            $totalCharges = $chargesQuery->sum('montant');

            /**
             * =========================
             * TOTAL VENTES
             * =========================
             */
            $totalVentes = $ventesDirectes + $paiementsCreances;

            /**
             * =========================
             * BENEFICE
             * =========================
             */
            $benefice = $totalVentes - $totalCharges;

            $data[] = [

                'mois'               => $start->translatedFormat('F'),

                'ventes_directes'    => (float) $ventesDirectes,

                'paiements_creances' => (float) $paiementsCreances,

                'total_ventes'       => (float) $totalVentes,

                'total_depenses'     => (float) $totalCharges,

                'benefice'           => (float) $benefice,
            ];
        }

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);

    } catch (Exception $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors du calcul du résumé mensuel.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



    public function getAll()
    {
        try {

            $operations = OperationCompte::visible()
                ->whereDoesntHave('typeOperation', function ($q) {
                    $q->where('nature', 2);
                })
                ->with([
                    'typeOperation',
                    'compte.station',
                    'createdBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => OperationCompteResource::collection($operations),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations.',
            ], 500);
        }
    }
/* la liste des operations orndinaires par date
*/
    public function getAllByPeriode(array $data)
    {
        try {

            $dateDebut = $data['date_debut'] ?? null;
            $dateFin   = $data['date_fin'] ?? null;

            $query = OperationCompte::visible()
                ->whereDoesntHave('typeOperation', function ($q) {
                    $q->where('nature', 2);
                })
                ->with([
                    'typeOperation',
                    'compte.station',
                    'createdBy',
                ]);

            // 🔹 Filtre par dates si fournies
            if ($dateDebut && $dateFin) {
                $query->whereBetween('created_at', [
                    $dateDebut . ' 00:00:00',
                    $dateFin . ' 23:59:59',
                ]);
            }

            $operations = $query
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => OperationCompteResource::collection($operations),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations par période.',
            ], 500);
        }
    }

    public function getAll1()
    {
        try {

            $operations = OperationCompte::visible()
                ->whereHas('typeOperation', fn($q) => $q->where('nature', 2))
                ->with([
                    'typeOperation',
                    'source.station',
                    'destination.station',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => OperationTransfertResource::collection($operations),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des transferts.',
            ], 500);
        }
    }
    /* la liste des operations inter comptes entre deux dates 
  */

    public function getAllTransfertsByPeriode(array $data)
    {
        try {

            $dateDebut = $data['date_debut'] ?? null;
            $dateFin   = $data['date_fin'] ?? null;

            $query = OperationCompte::visible()
                ->whereHas('typeOperation', fn($q) => $q->where('nature', 2))
                ->with([
                    'typeOperation',
                    'source.station',
                    'destination.station',
                    'createdBy',
                    'modifiedBy',
                ]);

            // 🔹 Filtre par période
            if ($dateDebut && $dateFin) {
                $query->whereBetween('created_at', [
                    $dateDebut . ' 00:00:00',
                    $dateFin . ' 23:59:59',
                ]);
            }

            $operations = $query
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => OperationTransfertResource::collection($operations),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des transferts par période.',
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $operation = OperationCompte::visible()
                ->with([
                    'typeOperation',
                    'compte.station',
                    'source.station',
                    'destination.station',
                    'createdBy',
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new OperationCompteResource($operation),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Opération introuvable.',
            ], 404);
        }
    }

    /**
     * OPÉRATION SIMPLE (ENTRÉE / SORTIE)
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $compte = Compte::lockForUpdate()->find($data['id_compte']);
            $type   = TypeOperation::find($data['id_type_operation']);

            if (! $compte || ! $type) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte ou type d’opération introuvable.',
                ], 404);
            }

            if ($type->nature === 2) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Utilisez le transfert inter-station. en precisant la source et la destination',
                ], 409);
            }

            if ($type->nature === 0 && $data['montant'] > $compte->solde_actuel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant.',
                ], 409);
            }

            $operation = OperationCompte::create([
                'id_compte'         => $compte->id,
                'id_type_operation' => $type->id,
                'montant'           => $data['montant'],
                'reference'         => $data['reference'] ?? null,
                'commentaire'       => $data['commentaire'] ?? null,
                'status'            => 'effectif',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Opération enregistrée.',
                'data'    => new OperationCompteResource(
                    $operation->load([
                        'typeOperation',
                        'compte.station',
                        'createdBy',
                    ])
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’opération.',
            ], 500);
        }
    }

    /**
     * TRANSFERT INTER-STATION
     */
    // public function transfer(array $data)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $source = Compte::lockForUpdate()->find($data['id_source']);
    //         $dest   = Compte::lockForUpdate()->find($data['id_destination']);

    //         if (! $source || ! $dest) {
    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Compte source ou destination introuvable.',
    //             ], 404);
    //         }

    //         if ($data['montant'] > $source->solde_actuel) {
    //             return response()->json([
    //                 'status'  => 409,
    //                 'message' => 'Solde insuffisant.',
    //             ], 409);
    //         }

    //         $type = TypeOperation::where('nature', 2)->first();

    //         if (! $type) {
    //             return response()->json([
    //                 'status'  => 500,
    //                 'message' => 'Type transfert non configuré.',
    //             ], 500);
    //         }

    //         $opSource = OperationCompte::create([
    //             'id_compte'         => $source->id,
    //             'id_source'         => $source->id,
    //             'id_destination'    => $dest->id,
    //             'id_type_operation' => $type->id,
    //             'montant'           => $data['montant'],
    //             'reference'         => $data['reference'] ?? null,
    //             'commentaire'       => $data['commentaire'] ?? null,
    //             'status'            => 'en_attente',
    //         ]);

    //         OperationCompte::create([
    //             'id_compte'         => $dest->id,
    //             'id_source'         => $source->id,
    //             'id_destination'    => $dest->id,
    //             'id_type_operation' => $type->id,
    //             'montant'           => $data['montant'],
    //             'reference'         => $opSource->reference,
    //             'commentaire'       => $data['commentaire'] ?? null,
    //             'status'            => 'en_attente',
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status'    => 201,
    //             'message'   => 'Transfert envoyé.',
    //             'reference' => $opSource->reference,
    //         ], 201);

    //     } catch (Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors du transfert.',
    //         ], 500);
    //     }
    // }

    public function transfer(array $data)
    {
        DB::beginTransaction();

        try {

            $source      = Compte::lockForUpdate()->find($data['id_source']);
            $destination = Compte::lockForUpdate()->find($data['id_destination']);

            if (! $source || ! $destination) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte source ou destination introuvable.',
                ], 404);
            }

            if ($source->id === $destination->id) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Un transfert vers le même compte est interdit.',
                ], 409);
            }

            $montant = (float) $data['montant'];

            if ($montant <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Montant invalide.',
                ], 409);
            }

            // 🔒 contrôle SOLDE AVANT transfert
            if ($montant > $source->solde_actuel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant sur le compte source.',
                ], 409);
            }

            $type = TypeOperation::where('nature', 2)->firstOrFail();

            $operation = OperationCompte::create([
                'id_compte'         => $source->id,
                'id_source'         => $source->id,
                'id_destination'    => $destination->id,
                'id_type_operation' => $type->id,
                'montant'           => $montant,
                'commentaire'       => $data['commentaire'] ?? null,
                'status'            => 'en_attente',
                // reference générée automatiquement par le model
            ]);

            DB::commit();

            return response()->json([
                'status'    => 201,
                'message'   => 'Transfert envoyé et en attente de confirmation.',
                'reference' => $operation->reference,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du transfert.',
            ], 500);
        }
    }

    // public function confirm(string $reference)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $operations = OperationCompte::where('reference', $reference)
    //             ->lockForUpdate()
    //             ->get();

    //         if ($operations->count() !== 2) {
    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Transfert introuvable.',
    //             ], 404);
    //         }

    //         foreach ($operations as $op) {
    //             $op->update(['status' => 'effectif']);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Transfert confirmé.',
    //         ], 200);

    //     } catch (Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la confirmation.',
    //         ], 500);
    //     }
    // }
    public function confirm(string $reference)
    {
        DB::beginTransaction();

        try {

            $op = OperationCompte::lockForUpdate()
                ->where('reference', $reference)
                ->where('status', 'en_attente')
                ->first();

            if (! $op) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Transfert introuvable ou déjà traité.',
                ], 404);
            }

            // dernier contrôle solde
            if ($op->montant > $op->source->solde_actuel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant au moment de la confirmation.',
                ], 409);
            }

            $op->update(['status' => 'effectif']);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Transfert confirmé avec succès.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la confirmation.',
            ], 500);
        }
    }

    public function cancel(string $reference)
    {
        DB::beginTransaction();

        try {

            $operations = OperationCompte::where('reference', $reference)
                ->lockForUpdate()
                ->get();

            if ($operations->isEmpty()) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Transfert introuvable.',
                ], 404);
            }

            foreach ($operations as $op) {
                $op->update(['status' => 'annule']);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Transfert annulé.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’annulation.',
            ], 500);
        }
    }
}
