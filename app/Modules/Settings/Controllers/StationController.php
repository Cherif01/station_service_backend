<?php
namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\StoreStationRequest;
use App\Modules\Settings\Requests\UpdateStationRequest;
use App\Modules\Settings\Services\StationService;

class StationController extends Controller
{
    public function __construct(private StationService $service)
    {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function store(StoreStationRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateStationRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }

    public function activateStation(int $id)
    {
        return $this->service->activateStation($id);
    }

}
