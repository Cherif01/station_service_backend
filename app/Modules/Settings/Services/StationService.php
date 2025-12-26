<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Station;
use App\Modules\Settings\Resources\StationResource;
use App\Modules\Settings\Services\RoleFilterService;
use Exception;

class StationService
{

    public function getAll()
    {
        try {

            // 🔹 Requête de base avec TOUTES les relations nécessaires
            $query = Station::with([
                'ville',
                'pompes', // ✅ pompes de la station
                'createdBy',
                'modifiedBy',
            ]);

            /**
             * 🔹 Filtrage par rôle (relation-based)
             *
             * - super_admin  → toutes les stations + leurs pompes
             * - superviseur  → stations de sa ville + leurs pompes
             * - admin/gerant → sa station + ses pompes
             * - pompiste     → aucune station
             */
            $query = RoleFilterService::apply($query, [
                'station_relation' => null,
            ]);

            // 🔹 Exécution
            $stations = $query->get();

            return response()->json([
                'status' => 200,
                'data'   => StationResource::collection($stations),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des stations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function store(array $data)
    {
        try {

            $station = Station::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Station créée avec succès.',
                'data'    => new StationResource($station),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $station = Station::findOrFail($id);
            $station->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Station modifiée avec succès.',
                'data'    => new StationResource($station),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {

            Station::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Station supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
