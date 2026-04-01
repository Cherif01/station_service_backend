<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Requests\StoreCompteDirectionRequest;
use App\Modules\Caisse\Requests\UpdateCompteDirectionRequest;
use App\Modules\Caisse\Services\CompteDirectionService;

class CompteDirectionController extends Controller
{
    public function __construct(private CompteDirectionService $service)
    {
    }

    public function index()
    {
        return $this->service->getAll();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(StoreCompteDirectionRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateCompteDirectionRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
