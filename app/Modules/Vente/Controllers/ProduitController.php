<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreProduitRequest;
use App\Modules\Vente\Requests\UpdateProduitRequest;
use App\Modules\Vente\Services\ProduitService;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function __construct(
        protected ProduitService $service
    ) {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(StoreProduitRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateProduitRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }

     public function calculerStockJournalierToutesCuves()
    {
        return $this->service->calculerToutesCuves();
    }
    //calculerToutesCuvesEntreDates
   

public function calculerToutesCuvesEntreDates(Request $request)
{
    return $this->service->calculerToutesCuvesEntreDates(
        $request->query('date_debut'),
        $request->query('date_fin')
    );
}

    
}
