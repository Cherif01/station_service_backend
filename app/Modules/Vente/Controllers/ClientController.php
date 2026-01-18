<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreClientRequest;
use App\Modules\Vente\Requests\UpdateClientRequest;
use App\Modules\Vente\Services\ClientService;

class ClientController extends Controller
{
    protected ClientService $service;

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->index();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(StoreClientRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateClientRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
