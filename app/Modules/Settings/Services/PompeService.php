<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Pompe;
use App\Modules\Settings\Resources\PompeResource;
use Exception;

class PompeService
{

    public function getAll()
    {
        try {

            // 🔹 Requête SIMPLE
            // Le filtrage par rôle est AUTOMATIQUE via le Global Scope du modèle Pompe
            $pompes = Pompe::with([
                'station',
                'createdBy',
                'modifiedBy',
            ])
                ->orderBy('reference')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => PompeResource::collection($pompes),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des pompes.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function store(array $data)
    {
        try {

            $pompe = Pompe::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe créée avec succès.',
                'data'    => new PompeResource($pompe),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $pompe = Pompe::findOrFail($id);
            $pompe->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe modifiée avec succès.',
                'data'    => new PompeResource($pompe),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {

            Pompe::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
