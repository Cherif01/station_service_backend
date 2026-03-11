<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Pays;
use App\Modules\Settings\Resources\PaysResource;
use Exception;

class PaysService
{
    /**
     * ============================
     * Créer un pays
     * ============================
     */
    public function store(array $data)
    {
        try {

            $pays = Pays::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pays créé avec succès.',

            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du pays.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Mettre à jour un pays
     * ============================
     */
    public function update(int $id, array $data)
    {
        try {

            $pays = Pays::findOrFail($id);
            $pays->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pays mis à jour avec succès.',

            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du pays.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Supprimer un pays
     * ============================
     */
    public function delete(int $id)
    {
        try {

            $pays = Pays::findOrFail($id);
            $pays->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Pays supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du pays.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Liste des pays
     * ============================
     */
    public function getAll()
    {
        try {

            $pays = Pays::with([
                'createdBy',
                'modifiedBy',
            ])
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => PaysResource::collection($pays),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des pays.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

}
