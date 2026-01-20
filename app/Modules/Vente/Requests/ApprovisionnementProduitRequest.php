<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovisionnementProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_produit'      => ['required', 'exists:produits,id'],
            'qte_appro'    => ['required', 'numeric', 'min:0.01'],
            'pu_unitaire'  => ['required', 'numeric', 'min:0'],
            'commentaire'  => ['nullable', 'string'],
        ];
    }
}
