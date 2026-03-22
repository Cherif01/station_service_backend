<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\JaugeageCuve;
use App\Modules\Vente\Resources\JaugeageCuveResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class JaugeageCuveService
{
    /**
     * =========================
     * LISTE DES JAUGEAGES
     * =========================
     */
    public function getAll()
    {
        try {

            $items = JaugeageCuve::visible()
                ->with(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => JaugeageCuveResource::collection($items),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des jaugeages.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $item = JaugeageCuve::visible()
                ->with(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new JaugeageCuveResource($item),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Jaugeage introuvable.',
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION JAUGEAGE
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $hauteur = (float) ($data['hauteur'] ?? 0);

            if ($hauteur < 0) {

                return response()->json([
                    'status'  => 422,
                    'message' => 'Hauteur de cuve invalide.',
                ], 422);
            }

            $cuve = Cuve::visible()->find($data['id_cuve']);

            if (! $cuve) {

                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable ou non autorisée.',
                ], 404);
            }

            $jaugeage = JaugeageCuve::create([
                'id_cuve'       => $cuve->id,
                'hauteur'       => $hauteur,
                'volume_mesure' => $data['volume_mesure'] ?? 0,
                'commentaire'   => $data['commentaire'] ?? null,
                'status'        => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Jaugeage enregistré avec succès.',
                'data'    => new JaugeageCuveResource(
                    $jaugeage->load(['cuve', 'station', 'createdBy', 'modifiedBy'])
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l\'enregistrement du jaugeage.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION JAUGEAGE
     * =========================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $jaugeage = JaugeageCuve::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $jaugeage) {

                return response()->json([
                    'status'  => 404,
                    'message' => 'Jaugeage introuvable.',
                ], 404);
            }

            $jaugeage->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Jaugeage supprimé avec succès.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du jaugeage.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
