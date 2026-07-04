<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCharge;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Caisse\Resources\OperationCompteResource;
use App\Modules\Caisse\Resources\OperationTransfertResource;
use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Creance;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

                $ventesQuery    = LigneVente::query()->where('status', true)->whereBetween('created_at', [$start, $end]);
                $paiementsQuery = Paiement::query()->whereBetween('created_at', [$start, $end]);
                $chargesQuery   = OperationCharge::query()->whereBetween('created_at', [$start, $end]);
                $approsQuery    = ApprovisionnementCuve::query()->where('type_appro', 'approvisionnement')->whereBetween('created_at', [$start, $end]);
                $creancesQuery  = Creance::query()->whereBetween('created_at', [$start, $end]);

                if ($user->role !== 'super_admin') {

                    $stationId = request()->attributes->get('station_active_id');

                    $ventesQuery->where('id_station', $stationId);
                    $paiementsQuery->whereHas('creance', fn ($q) => $q->where('id_station', $stationId));
                    $chargesQuery->where('id_station', $stationId);
                    $approsQuery->where('id_station', $stationId);
                    $creancesQuery->where('id_station', $stationId);
                }

                $ventesResult      = $ventesQuery->selectRaw('SUM(qte_vendu * prix_unitaire) as ca, SUM(qte_vendu) as litres')->first();
                $ventesDirectes    = (float) ($ventesResult->ca ?? 0);
                $litresVendus      = (float) ($ventesResult->litres ?? 0);

                $paiementsCreances = (float) $paiementsQuery->sum('montant_payer');
                $totalCharges      = (float) $chargesQuery->sum('montant');
                $totalAppro        = (float) $approsQuery->sum(DB::raw('qte_appro * pu_unitaire'));
                $totalCreances     = (float) $creancesQuery->sum('montant');

                $margeBrute  = $litresVendus * 400;
                $totalVentes = $ventesDirectes + $paiementsCreances;
                $resultat    = $margeBrute - $totalCharges;
                $benefice    = $totalVentes - $totalCharges;

                $data[] = [
                    'mois'               => $start->translatedFormat('F'),
                    'ventes_directes'    => $ventesDirectes,
                    'paiements_creances' => $paiementsCreances,
                    'total_ventes'       => $totalVentes,
                    'litres_vendus'      => $litresVendus,
                    'total_appro'        => $totalAppro,
                    'marge_brute'        => $margeBrute,
                    'total_depenses'     => $totalCharges,
                    'total_creances'     => $totalCreances,
                    'resultat'           => $resultat,
                    'benefice'           => $benefice,
                ];
            }

            return response()->json([
                'status' => 200,
                'data'   => $data,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du calcul du résumé mensuel.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // public function getAll()
    // {
    //     try {

    //         $operations = OperationCompte::visible()
    //             ->whereDoesntHave('typeOperation', function ($q) {
    //                 $q->where('nature', 2);
    //             })
    //             ->with(['typeOperation', 'compte.station', 'createdBy'])
    //             ->orderByDesc('created_at')
    //             ->get();

    //         return response()->json([
    //             'status' => 200,
    //             'data'   => OperationCompteResource::collection($operations),
    //         ], 200);

    //     } catch (Throwable $e) {

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la récupération des opérations.',
    //         ], 500);
    //     }
    // }

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
                'source.station',
                'destination.station',
                'createdBy',
                'modifiedBy',
            ])
            ->get();

        $paiements = Paiement::visible()
            ->with([
                'creance.client',
                'compte.station',
                'createdBy',
            ])
            ->get();

        $creances = Creance::visible()
            ->with(['client', 'createdBy'])
            ->get();

        $data = $operations
            ->concat($paiements)
            ->concat($creances)
            ->sortByDesc(fn ($item) => $item->created_at)
            ->values();

        return response()->json([
            'status' => 200,
            'data'   => OperationCompteResource::collection($data),
        ], 200);

    } catch (Throwable $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des opérations.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function getAllByPeriode(array $data)
    {
        try {

            $dateDebut = $data['date_debut'] ?? null;
            $dateFin   = $data['date_fin'] ?? null;

            $query = OperationCompte::visible()
                ->whereDoesntHave('typeOperation', function ($q) {
                    $q->where('nature', 2);
                })
                ->with(['typeOperation', 'compte.station', 'createdBy']);

            if ($dateDebut && $dateFin) {
                $query->whereBetween('created_at', [
                    $dateDebut . ' 00:00:00',
                    $dateFin . ' 23:59:59',
                ]);
            }

            return response()->json([
                'status' => 200,
                'data'   => OperationCompteResource::collection(
                    $query->orderByDesc('created_at')->get()
                ),
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
                ->whereHas('typeOperation', fn ($q) => $q->where('nature', 2))
                ->with(['typeOperation', 'source.station', 'destination.station', 'createdBy', 'modifiedBy'])
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

    public function getAllTransfertsByPeriode(array $data)
    {
        try {

            $dateDebut = $data['date_debut'] ?? null;
            $dateFin   = $data['date_fin'] ?? null;

            $query = OperationCompte::visible()
                ->whereHas('typeOperation', fn ($q) => $q->where('nature', 2))
                ->with(['typeOperation', 'source.station', 'destination.station', 'createdBy', 'modifiedBy']);

            if ($dateDebut && $dateFin) {
                $query->whereBetween('created_at', [
                    $dateDebut . ' 00:00:00',
                    $dateFin . ' 23:59:59',
                ]);
            }

            return response()->json([
                'status' => 200,
                'data'   => OperationTransfertResource::collection(
                    $query->orderByDesc('created_at')->get()
                ),
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
                ->with(['typeOperation', 'compte.station', 'source.station', 'destination.station', 'createdBy'])
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
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte ou type d\'opération introuvable.',
                ], 404);
            }

            if ($type->nature === 2) {
                DB::rollBack();
                return response()->json([
                    'status'  => 409,
                    'message' => 'Utilisez le transfert inter-station en précisant la source et la destination.',
                ], 409);
            }

            if ($type->nature === 0 && $data['montant'] > $compte->solde_actuel) {
                DB::rollBack();
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
                    $operation->load(['typeOperation', 'compte.station', 'createdBy'])
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l\'opération.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * TRANSFERT INTER-STATION
     */
    public function transfer(array $data)
    {
        DB::beginTransaction();

        try {

            $source      = Compte::lockForUpdate()->find($data['id_source']);
            $destination = Compte::lockForUpdate()->find($data['id_destination']);

            if (! $source || ! $destination) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte source ou destination introuvable.',
                ], 404);
            }

            if ($source->id === $destination->id) {
                DB::rollBack();
                return response()->json([
                    'status'  => 409,
                    'message' => 'Un transfert vers le même compte est interdit.',
                ], 409);
            }

            $montant = (float) $data['montant'];

            if ($montant <= 0) {
                DB::rollBack();
                return response()->json([
                    'status'  => 409,
                    'message' => 'Montant invalide.',
                ], 409);
            }

            if ($montant > $source->solde_actuel) {
                DB::rollBack();
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
            ]);

            DB::commit();

            return response()->json([
                'status'    => 201,
                'message'   => 'Transfert envoyé et en attente de confirmation.',
                'reference' => $operation->reference,
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du transfert.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function confirm(string $reference)
    {
        DB::beginTransaction();

        try {

            $op = OperationCompte::lockForUpdate()
                ->where('reference', $reference)
                ->where('status', 'en_attente')
                ->first();

            if (! $op) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Transfert introuvable ou déjà traité.',
                ], 404);
            }

            if ($op->montant > $op->source->solde_actuel) {
                DB::rollBack();
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

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la confirmation.',
                'error'   => $e->getMessage(),
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
                DB::rollBack();
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
                'message' => 'Erreur lors de l\'annulation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
