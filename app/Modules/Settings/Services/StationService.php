<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Station;
use App\Modules\Settings\Resources\StationResource;
use Exception;

class StationService
{
    /**
     * =================================================
     * 🔹 LISTE DES STATIONS
     * =================================================
     */
    public function getAll()
    {
        try {

            // 🔹 Filtrage AUTOMATIQUE via GlobalScope Station
            $stations = Station::with([
                'ville.pays',
                'pompes',
                'parametrage',
                'createdBy',
                'modifiedBy',
            ])
            ->orderBy('libelle')
            ->get();

            return response()->json([
                'status' => 200,
                'data'   => StationResource::collection($stations),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des stations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =================================================
     * 🔹 DÉTAIL D’UNE STATION
     * =================================================
     */
    public function getOne(int $id)
    {
        try {

            // 🔹 Respect du GlobalScope (sécurité)
            $station = Station::with([
                'ville.pays',
                'pompes',
                'parametrage',
                'createdBy',
                'modifiedBy',
            ])->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new StationResource($station),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Station introuvable ou accès non autorisé.',
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de la station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =================================================
     * 🔹 CRÉATION
     * =================================================
     */
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

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Station introuvable ou accès non autorisé.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION
     * =================================================
     */
    public function delete(int $id)
    {
        try {

            Station::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Station supprimée avec succès.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Station introuvable ou accès non autorisé.',
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
