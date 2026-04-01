<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Caisse\Resources\TypeOperationResource;
use Throwable;

class TypeOperationService
{
   public function getAll()
{
    try {

        // =================================================
        // 🔹 INITIALISATION AUTOMATIQUE (UNE SEULE FOIS)
        // =================================================
        if (!TypeOperation::exists()) {

           TypeOperation::insert([
                [
                    'libelle'     => 'Entrée',
                    'commentaire' => 'Opération d’entrée de fonds',
                    'nature'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'libelle'     => 'Sortie',
                    'commentaire' => 'Opération de sortie de fonds',
                    'nature'      => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'libelle'     => 'Versement / Transfert de fonds',
                    'commentaire' => 'Versement vers la Direction ou transfert de fonds',
                    'nature'      => 2,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }

        // =================================================
        // 🔹 RÉCUPÉRATION
        // =================================================
        $types = TypeOperation::orderBy('nature')->get();

        return response()->json([
            'status' => 200,
            'data'   =>TypeOperationResource::collection($types),
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des types d’opération.',
        ], 500);
    }
}


    public function getOne(int $id)
    {
        try {

            $type = TypeOperation::findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new TypeOperationResource($type),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Type d’opération introuvable.',
            ], 404);
        }
    }

    public function store(array $data)
    {
        try {

            $type = TypeOperation::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Type d’opération créé avec succès.',
                'data'    => new TypeOperationResource($type),
            ], 201);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du type d’opération.',
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $type = TypeOperation::findOrFail($id);
            $type->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Type d’opération mis à jour.',
                'data'    => new TypeOperationResource($type->fresh()),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Type d’opération introuvable.',
            ], 404);
        }
    }

    public function delete(int $id)
    {
        try {

            TypeOperation::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Type d’opération supprimé.',
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Type d’opération introuvable.',
            ], 404);
        }
    }
}
