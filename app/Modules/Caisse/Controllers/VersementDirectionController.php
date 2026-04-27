<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Requests\ConfirmVersementDirectionRequest;
use App\Modules\Caisse\Requests\StoreVersementDirectionRequest;
use App\Modules\Caisse\Services\VersementDirectionService;
use Illuminate\Http\Request;

class VersementDirectionController extends Controller
{
    public function __construct(private VersementDirectionService $service)
    {
    }

    /**
     * Liste des versements direction — paramètres optionnels : date_debut, date_fin (défaut : aujourd'hui)
     */
    public function index(Request $request)
    {
        return $this->service->getAll(
            $request->only(['date_debut', 'date_fin'])
        );
    }

    /**
     * Initier un versement vers la Direction
     */
    public function initier(StoreVersementDirectionRequest $request)
    {
        return $this->service->initier($request->validated());
    }

    /**
     * Confirmer (valider) un versement — admin / super_admin uniquement
     */
    public function confirmer(ConfirmVersementDirectionRequest $request)
    {
        return $this->service->confirmer($request->validated()['reference']);
    }

    /**
     * Annuler un versement — admin / super_admin uniquement
     */
    public function annuler(ConfirmVersementDirectionRequest $request)
    {
        return $this->service->annuler($request->validated()['reference']);
    }
}
