<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;
use Throwable;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * =================================================
     * 🔹 DASHBOARD PRINCIPAL
     * =================================================
     */
    public function index()
    {
        try {

            $data = $this->dashboardService->getDashboard();

            return response()->json([
                'status' => 200,
                'data'   => $data,
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du chargement du tableau de bord.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
