<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\CompteDirection;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Caisse\Resources\VersementDirectionResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class VersementDirectionService
{
    /**
     * =================================================
     * LISTE DES VERSEMENTS DIRECTION
     * =================================================
     * - admin / super_admin : tous les versements (toutes stations)
     * - gérant / superviseur : versements de leur station uniquement
     */
    public function getAll()
    {
        try {

            $query = $this->buildVisibleQuery();

            $versements = $query
                ->with([
                    'source.station',
                    'compteDirection',
                    'typeOperation',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => VersementDirectionResource::collection($versements),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des versements.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllByPeriode(array $data)
    {
        try {

            $query = $this->buildVisibleQuery()
                ->with([
                    'source.station',
                    'compteDirection',
                    'typeOperation',
                    'createdBy',
                    'modifiedBy',
                ]);

            if (! empty($data['date_debut']) && ! empty($data['date_fin'])) {
                $query->whereBetween('created_at', [
                    $data['date_debut'] . ' 00:00:00',
                    $data['date_fin'] . ' 23:59:59',
                ]);
            }

            return response()->json([
                'status' => 200,
                'data'   => VersementDirectionResource::collection(
                    $query->orderByDesc('created_at')->get()
                ),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des versements par période.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * INITIER UN VERSEMENT (gérant / superviseur / admin)
     * =================================================
     */
    public function initier(array $data)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            // 🔒 Seuls les rôles pouvant initier un versement
            if (! in_array($user->role, ['gerant', 'superviseur', 'admin'])) {
                DB::rollBack();
                return response()->json([
                    'status'  => 403,
                    'message' => 'Vous n\'êtes pas autorisé à initier un versement.',
                ], 403);
            }

            // Résolution automatique de la station active
            $stationId = request()->attributes->get('station_active_id')
                ?? $user->affectations()->where('status', true)->latest('created_at')->value('id_station');

            if (! $stationId) {
                DB::rollBack();
                return response()->json([
                    'status'  => 403,
                    'message' => 'Aucune station active trouvée pour cet utilisateur.',
                ], 403);
            }

            // Récupération automatique du compte de la station
            $source = Compte::lockForUpdate()
                ->where('id_station', $stationId)
                ->first();

            if (! $source) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Aucun compte trouvé pour cette station.',
                ], 404);
            }

            $compteDirection = CompteDirection::find($data['id_compte_direction']);

            if (! $compteDirection) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte direction introuvable.',
                ], 404);
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

            $type = TypeOperation::where('nature', 2)->first();

            if (! $type) {
                DB::rollBack();
                return response()->json([
                    'status'  => 500,
                    'message' => 'Type opération "Versement/Transfert" non configuré. Contactez l\'administrateur.',
                ], 500);
            }

            $versement = OperationCompte::create([
                'id_compte'           => $source->id,
                'id_source'           => $source->id,
                'id_destination'      => null,
                'id_compte_direction' => $compteDirection->id,
                'id_type_operation'   => $type->id,
                'montant'             => $montant,
                'mode'                => $data['mode'],
                'commentaire'         => $data['commentaire'] ?? null,
                'status'              => 'en_attente',
            ]);

            DB::commit();

            return response()->json([
                'status'    => 200,
                'message'   => 'Versement initié, en attente de validation par la Direction.',
                'reference' => $versement->reference,
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l\'initiation du versement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * CONFIRMER UN VERSEMENT (admin / super_admin uniquement)
     * =================================================
     */
    public function confirmer(string $reference)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            if (! in_array($user->role, ['admin', 'super_admin'])) {
                DB::rollBack();
                return response()->json([
                    'status'  => 403,
                    'message' => 'Seul un administrateur peut valider un versement.',
                ], 403);
            }

            $versement = OperationCompte::lockForUpdate()
                ->whereNotNull('id_compte_direction')
                ->where('reference', $reference)
                ->where('status', 'en_attente')
                ->first();

            if (! $versement) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Versement introuvable ou déjà traité.',
                ], 404);
            }

            // Re-vérifier le solde source au moment de la confirmation
            if ($versement->montant > $versement->source->solde_actuel) {
                DB::rollBack();
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant sur le compte source au moment de la confirmation.',
                ], 409);
            }

            $versement->update(['status' => 'effectif']);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Versement validé avec succès.',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la validation du versement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * ANNULER UN VERSEMENT (admin / super_admin uniquement)
     * =================================================
     */
    public function annuler(string $reference)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            if (! in_array($user->role, ['admin', 'super_admin'])) {
                DB::rollBack();
                return response()->json([
                    'status'  => 403,
                    'message' => 'Seul un administrateur peut annuler un versement.',
                ], 403);
            }

            $versement = OperationCompte::lockForUpdate()
                ->whereNotNull('id_compte_direction')
                ->where('reference', $reference)
                ->where('status', 'en_attente')
                ->first();

            if (! $versement) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Versement introuvable ou déjà traité.',
                ], 404);
            }

            $versement->update(['status' => 'annule']);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Versement annulé.',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l\'annulation du versement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * QUERY DE BASE : VISIBILITÉ PAR RÔLE
     * =================================================
     */
    private function buildVisibleQuery()
    {
        $user  = Auth::user();
        $query = OperationCompte::whereNotNull('id_compte_direction');

        // admin / super_admin → tous les versements direction (toutes stations)
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $query;
        }

        // gérant / superviseur → uniquement leur station
        $stationId = request()->attributes->get('station_active_id')
            ?? $user->affectations()->where('status', true)->latest('created_at')->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('source', function ($q) use ($stationId) {
            $q->where('id_station', $stationId);
        });
    }
}
