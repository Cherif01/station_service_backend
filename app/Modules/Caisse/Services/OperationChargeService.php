<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\OperationCharge;
use App\Modules\Caisse\Resources\OperationChargeCollection;
use App\Modules\Caisse\Resources\OperationChargeResource;
use Exception;

class OperationChargeService
{
    private array $with = [
        'station:id,libelle',
        'chargeCategory:id,libelle',
        'compte:id,libelle',
        'createdBy:id,name',
        'modifiedBy:id,name',
    ];

    public function getAll()
    {
        try {

            $items = OperationCharge::visible()
                ->with($this->with)
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => new OperationChargeCollection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur récupération charges.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $item = OperationCharge::visible()
                ->with($this->with)
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new OperationChargeResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Charge introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public function store(array $data)
    {
        try {

            $stationActiveId = request()->attributes->get('station_active_id');

            if (! $stationActiveId) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Aucune station active détectée.',
                ], 400);
            }

            $data['id_station'] = $stationActiveId;

            $item = OperationCharge::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Charge enregistrée.',
                'data'    => new OperationChargeResource($item->load($this->with)),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur enregistrement charge.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $item = OperationCharge::visible()->findOrFail($id);

            $item->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Charge modifiée.',
                'data'    => new OperationChargeResource($item->load($this->with)),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur modification charge.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {

            $item = OperationCharge::visible()->findOrFail($id);

            $item->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Charge supprimée.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression charge.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
