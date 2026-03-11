<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Fournisseur;
use App\Modules\Settings\Resources\FournisseurCollection;
use App\Modules\Settings\Resources\FournisseurResource;
use Exception;

class FournisseurService
{

    /**
     * =========================
     * LISTE DES FOURNISSEURS
     * =========================
     */
    public function getAll()
    {
        try {

            $items = Fournisseur::orderBy('raison_sociale')->get();

            return response()->json([
                'status' => 200,
                'data'   => new FournisseurCollection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des fournisseurs.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL FOURNISSEUR
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $item = Fournisseur::findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new FournisseurResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Fournisseur introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION FOURNISSEUR
     * =========================
     */
    public function store(array $data)
    {
        try {

            $item = Fournisseur::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Fournisseur créé avec succès.',
                'data'    => new FournisseurResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du fournisseur.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MODIFICATION FOURNISSEUR
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $item = Fournisseur::findOrFail($id);

            $item->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Fournisseur modifié avec succès.',
                'data'    => new FournisseurResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification du fournisseur.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION FOURNISSEUR
     * =========================
     */
    public function delete(int $id)
    {
        try {

            $item = Fournisseur::findOrFail($id);

            $item->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Fournisseur supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du fournisseur.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}