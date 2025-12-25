<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Resources\AffectationResource;
use Exception;

class AffectationService
{
    public function getAll()
    {
        try {

            $affectations = Affectation::with([
                'pompe',
                'pompiste',
                'station',
                'createdBy',
                'modifiedBy'
            ])->get();

            return response()->json([
                'status' => 200,
                'data'   => AffectationResource::collection($affectations),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des affectations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function store(array $data)
    {
        try {

            $affectation = Affectation::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Affectation créée avec succès.',
                'data'    => new AffectationResource($affectation),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’affectation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $affectation = Affectation::findOrFail($id);
            $affectation->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Affectation modifiée avec succès.',
                'data'    => new AffectationResource($affectation),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de l’affectation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {

            Affectation::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Affectation supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’affectation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
