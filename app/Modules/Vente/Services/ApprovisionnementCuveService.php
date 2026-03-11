<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Resources\ApprovisionnementCuveResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApprovisionnementCuveService
{
    /**
     * =========================
     * LISTE
     * =========================
     */
    public function getAll()
    {
        try {

            $items = ApprovisionnementCuve::visible()
                ->with(['cuve', 'station'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ApprovisionnementCuveResource::collection($items),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des approvisionnements.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * DETAIL
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $item = ApprovisionnementCuve::visible()
                ->with(['cuve', 'station'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ApprovisionnementCuveResource($item),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Approvisionnement introuvable.',
            ]);
        }
    }

    /**
     * =========================
     * APPROVISIONNEMENT
     * =========================
     */
    public function store(array $data)
    {
        try {

            $cuve = Cuve::findOrFail($data['id_cuve']);

            $appro = ApprovisionnementCuve::create([
                'id_station'   => request()->attributes->get('station_active_id'),
                'id_cuve'      => $cuve->id,
                'qte_appro'    => $data['qte_appro'],
                'pu_unitaire'  => $data['pu_unitaire'] ?? 0,
                'type_appro'   => 'approvisionnement',
                'commentaire'  => $data['commentaire'] ?? null,
            ]);

            return response()->json([
                'status'  => 201,
                'message' => 'Approvisionnement enregistré avec succès.',
                'data'    => new ApprovisionnementCuveResource(
                    $appro->load(['cuve', 'station'])
                ),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’approvisionnement.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * RETOUR CUVE
     * =========================
     */
    public function retourcuve(array $data)
    {
        try {

            $cuve = Cuve::findOrFail($data['id_cuve']);

            $retour = ApprovisionnementCuve::create([
                'id_station'   => request()->attributes->get('station_active_id'),
                'id_cuve'      => $cuve->id,
                'qte_appro'    => $data['qte_appro'],
                'pu_unitaire'  => $data['pu_unitaire'] ?? 0,
                'type_appro'   => 'retour_cuve',
                'commentaire'  => $data['commentaire'] ?? null,
            ]);

            return response()->json([
                'status'  => 201,
                'message' => 'Retour de cuve enregistré.',
                'data'    => new ApprovisionnementCuveResource(
                    $retour->load(['cuve', 'station'])
                ),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement du retour de cuve.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * UPDATE
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $appro = ApprovisionnementCuve::visible()->findOrFail($id);

            $appro->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement modifié avec succès.',
                'data'    => new ApprovisionnementCuveResource($appro),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * DELETE
     * =========================
     */
    public function delete(int $id)
    {
        try {

            $appro = ApprovisionnementCuve::visible()->findOrFail($id);

            $appro->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement supprimé.',
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}