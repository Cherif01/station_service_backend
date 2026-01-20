<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Resources\CompteResource;
use Throwable;

class CompteService
{
    public function getAll()
    {
        try {

            $comptes = Compte::visible()
                ->with(['station.affectations.user', 'createdBy', 'modifiedBy'])
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => CompteResource::collection($comptes),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des comptes.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $compte = Compte::visible()
                ->with(['station', 'createdBy', 'modifiedBy'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new CompteResource($compte),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Compte introuvable.',
            ], 404);
        }
    }

    public function store(array $data)
    {
        try {

            $compte = Compte::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Compte créé avec succès.',
                'data'    => new CompteResource(
                    $compte->load(['station', 'createdBy'])
                ),
            ], 201);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du compte.',
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $compte = Compte::visible()->findOrFail($id);
            $compte->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Compte mis à jour.',
                'data'    => new CompteResource(
                    $compte->fresh()->load(['station', 'createdBy', 'modifiedBy'])
                ),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Compte introuvable.',
            ], 404);
        }
    }

    public function delete(int $id)
    {
        try {

            $compte = Compte::visible()->findOrFail($id);
            $compte->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Compte supprimé.',
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Compte introuvable.',
            ], 404);
        }
    }
}
