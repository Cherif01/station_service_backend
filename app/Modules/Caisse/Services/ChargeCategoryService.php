<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\ChargeCategory;
use App\Modules\Caisse\Resources\ChargeCategoryCollection;
use App\Modules\Caisse\Resources\ChargeCategoryResource;
use Exception;

class ChargeCategoryService
{
    public function getAll()
    {
        try {

            $items = ChargeCategory::visible()
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data' => new ChargeCategoryCollection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des catégories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $item = ChargeCategory::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data' => new ChargeCategoryResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 404,
                'message' => 'Catégorie introuvable.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function store(array $data)
    {
        try {

            $item = ChargeCategory::create($data);

            return response()->json([
                'status' => 200,
                'message' => 'Catégorie créée avec succès.',
                'data' => new ChargeCategoryResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la création.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $item = ChargeCategory::visible()->findOrFail($id);

            $item->update($data);

            return response()->json([
                'status' => 200,
                'message' => 'Catégorie modifiée.',
                'data' => new ChargeCategoryResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la modification.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {

            $item = ChargeCategory::visible()->findOrFail($id);

            $item->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Catégorie supprimée.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}