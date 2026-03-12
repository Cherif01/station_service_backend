<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreJaugeageRequest;
use App\Modules\Vente\Services\JaugeageCuveService;
use Illuminate\Http\JsonResponse;

class JaugeageController extends Controller
{
    public function __construct(
        private readonly JaugeageCuveService $service
    ) {}

    /**
     * =========================
     * LISTE DES JAUGEAGES
     * =========================
     */
    public function index(): JsonResponse
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL
     * =========================
     */
    public function show(int $id): JsonResponse
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * CRÉATION
     * =========================
     */
    public function store(StoreJaugeageRequest $request): JsonResponse
    {
        return $this->service->store($request->validated());
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
