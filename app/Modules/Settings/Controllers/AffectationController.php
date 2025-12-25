<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Services\AffectationService;
use App\Modules\Settings\Requests\StoreAffectationRequest;
use App\Modules\Settings\Requests\UpdateAffectationRequest;

class AffectationController extends Controller
{
    public function __construct(private AffectationService $service) {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function store(StoreAffectationRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateAffectationRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
