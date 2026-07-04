<?php
namespace App\Modules\Settings\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Settings\Models\Station;
use App\Modules\Settings\Resources\StationResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;

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

            // 🔹 Filtrage EXPLICITE via scopeVisible
            $stations = Station::visible()
                ->with([
                    'ville.pays',
                    'pompes',
                    'parametrage',

                    // ✅ AJOUT : affectations de la station
                    'affectations.user',
                    'affectations.pompe',

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

            $station = Station::visible()
                ->with([
                    'ville.pays',
                    'pompes',
                    'parametrage',

                    // ✅ AJOUT : affectations de la station
                    'affectations.user',
                    'affectations.pompe',

                    'createdBy',
                    'modifiedBy',
                ])
                ->findOrFail($id);

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
    // public function store(array $data)
    // {
    //     try {

    //         $station = Station::create($data);

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Station créée avec succès.',
    //             'data'    => new StationResource($station),
    //         ]);

    //     } catch (Exception $e) {

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de la station.',
    //             'error'   => $e->getMessage(),
    //         ]);
    //     }
    // }

    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            /**
             * =================================================
             * 1️⃣ CRÉATION STATION
             * =================================================
             */
            $station = Station::create($data);

            /**
             * =================================================
             * 2️⃣ CRÉATION COMPTE STATION (AUTO)
             * =================================================
             */
            $compte = Compte::create([
                'id_station' => $station->id,
                'libelle'    => "Compte principal - {$station->libelle}",
                'numero'      => 'CPT-' . str_pad($station->id, 4, '0', STR_PAD_LEFT),
                'commentaire' => "Compte de la station {$station->libelle}",
                'solde_initial' => 0,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Station et compte créés avec succès.',

            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la station.',
                'error'   => $e->getMessage(),
            ], 500);
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

            $station = Station::visible()->findOrFail($id);
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

            Station::visible()->findOrFail($id)->delete();

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



  


public function activateStation(int $id)
{
    $tenantDb = request()->attributes->get('tenant_db', 'default');

    Cache::put(
        'station_active_user_' . Auth::id() . '_' . $tenantDb,
        $id,
        now()->addHours(12)
    );

    return response()->json([
        'status'  => 200,
        'message' => 'Station activée avec succès.',
    ]);
}

}
