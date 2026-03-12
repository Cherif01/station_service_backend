<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\PerteCuve;
use App\Modules\Vente\Resources\PerteCuveResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class PerteCuveService
{
    /**
     * =========================
     * LISTE DES PERTES
     * =========================
     */
    public function getAll()
    {
        try {

            $pertes = PerteCuve::visible()
                ->with(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => PerteCuveResource::collection($pertes),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des pertes de cuves.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D'UNE PERTE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $perte = PerteCuve::visible()
                ->with(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ->find($id);

            if (! $perte) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Perte de cuve introuvable.',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data'   => new PerteCuveResource($perte),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de la perte.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * ENREGISTRER UNE PERTE
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $idCuve = $data['id_cuve'] ?? null;
            $qte    = $data['quantite_perdue'] ?? null;

            if (! $idCuve || ! $qte) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'La cuve et la quantité perdue sont obligatoires.',
                ], 400);
            }

            $qte = (float) $qte;

            if ($qte <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'La quantité perdue doit être strictement positive.',
                ], 409);
            }

            $cuve = Cuve::find($idCuve);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            $perte = PerteCuve::create([
                'id_station'      => request()->attributes->get('station_active_id'),
                'id_cuve'         => $cuve->id,
                'quantite_perdue' => $qte,
                'commentaire'     => $data['commentaire'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Perte de cuve enregistrée.',
                'data'    => new PerteCuveResource(
                    $perte->load(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de l\'enregistrement de la perte.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $perte = PerteCuve::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $perte) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Perte de cuve introuvable.',
                ], 404);
            }

            $perte->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Perte supprimée avec succès.',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la perte.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
