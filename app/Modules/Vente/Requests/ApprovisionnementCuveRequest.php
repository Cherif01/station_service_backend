<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovisionnementCuveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cuve'      => ['required', 'exists:cuves,id'],
            'qte_appro'    => ['required', 'numeric', 'min:0.01'],
            'id_fournisseur' => [
                'nullable',
                'exists:fournisseurs,id'
            ],

            'pu_unitaire'  => ['required', 'numeric', 'min:0'],
            'commentaire'  => ['nullable', 'string'],
        ];
    }
}
