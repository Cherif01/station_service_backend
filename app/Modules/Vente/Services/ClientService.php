<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Client;
use App\Modules\Vente\Resources\ClientResource;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function index()
    {
        $clients = Client::visible()
            ->with(['station', 'createdBy', 'modifiedBy'])
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => ClientResource::collection($clients),
        ], 200);
    }

    public function getOne(int $id)
    {
        $client = Client::visible()
            ->with(['station', 'ventes', 'createdBy', 'modifiedBy'])
            ->find($id);

        if (! $client) {
            return response()->json([
                'status'  => 404,
                'message' => 'Client introuvable',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new ClientResource($client),
        ], 200);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {

            $client = Client::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Client créé avec succès',
                'data'    => new ClientResource(
                    $client->load(['station', 'createdBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur création client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {

            $client = Client::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $client) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Client introuvable',
                ], 404);
            }

            $client->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Client mis à jour',
                'data'    => new ClientResource(
                    $client->load(['station', 'createdBy', 'modifiedBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {

            $client = Client::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $client) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Client introuvable',
                ], 404);
            }

            $client->update(['status' => false]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Client désactivé',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
