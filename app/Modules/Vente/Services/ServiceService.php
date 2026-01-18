<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Service;
use App\Modules\Vente\Resources\ServiceResource;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    public function index()
    {
        $services = Service::visible()->get();

        return response()->json([
            'status' => 200,
            'data'   => ServiceResource::collection($services),
        ], 200);
    }

    public function getOne(int $id)
    {
        $service = Service::visible()->find($id);

        if (! $service) {
            return response()->json([
                'status'  => 404,
                'message' => 'Service introuvable',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new ServiceResource($service),
        ], 200);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {

            $service = Service::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Service créé avec succès',
                'data'    => new ServiceResource($service),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur création service',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {

            $service = Service::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $service) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Service introuvable',
                ], 404);
            }

            $service->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Service mis à jour',
                'data'    => new ServiceResource($service),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour service',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {

            $service = Service::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $service) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Service introuvable',
                ], 404);
            }

            $service->update(['status' => false]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Service désactivé',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression service',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
