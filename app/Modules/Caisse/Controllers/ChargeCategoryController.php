<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Requests\StoreChargeCategoryRequest;
use App\Modules\Caisse\Requests\UpdateChargeCategoryRequest;
use App\Modules\Caisse\Services\ChargeCategoryService;

class ChargeCategoryController extends Controller
{
    public function __construct(
        protected ChargeCategoryService $service
    ) {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(StoreChargeCategoryRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateChargeCategoryRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}