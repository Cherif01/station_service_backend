<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\CompteDirection;
use App\Modules\Caisse\Resources\CompteDirectionResource;
use Throwable;

class CompteDirectionService
{
    public function getAll()
    {
        try {

            $comptes = CompteDirection::with(['createdBy', 'modifiedBy'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => CompteDirectionResource::collection($comptes),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des comptes direction.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $compte = CompteDirection::with(['createdBy', 'modifiedBy'])->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new CompteDirectionResource($compte),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Compte direction introuvable.',
            ], 404);
        }
    }

    public function store(array $data)
    {
        try {

            $compte = CompteDirection::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Compte direction créé avec succès.',
                'data'    => new CompteDirectionResource($compte->load(['createdBy'])),
            ], 201);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du compte direction.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $compte = CompteDirection::findOrFail($id);
            $compte->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Compte direction mis à jour.',
                'data'    => new CompteDirectionResource($compte->fresh(['createdBy', 'modifiedBy'])),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {

            $compte = CompteDirection::findOrFail($id);

            if ($compte->versements()->where('status', 'en_attente')->exists()) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Impossible de supprimer : des versements en attente sont liés à ce compte.',
                ], 409);
            }

            $compte->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Compte direction supprimé.',
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
