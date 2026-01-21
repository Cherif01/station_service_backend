<?php
namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\StoreSettingRequest;
use App\Modules\Settings\Requests\UpdateSettingRequest;
use App\Modules\Settings\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $service
    ) {}

    /**
     * =================================================
     * LISTE DES SETTINGS
     * =================================================
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * =================================================
     * DÉTAIL D’UN SETTING
     * =================================================
     */
    public function show(int $id)
    {
        return $this->service->show($id);
    }

    /**
     * =================================================
     * CRÉATION
     * =================================================
     */
    public function store(StoreSettingRequest $request)
    {
        return $this->service->store(
            $request->validated()
        );
    }

    /**
     * =================================================
     * MISE À JOUR
     * =================================================
     */
    public function update(UpdateSettingRequest $request, int $id)
    {
        return $this->service->update(
            $id,
            $request->validated()
        );
    }

    /**
     * =================================================
     * SUPPRESSION
     * =================================================
     */
    public function destroy(int $id)
    {
        return $this->service->destroy($id);
    }
}
