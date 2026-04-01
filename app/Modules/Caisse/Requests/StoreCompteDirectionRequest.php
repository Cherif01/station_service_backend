<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero'        => ['required', 'string', 'max:50', 'unique:comptes_direction,numero'],
            'libelle'       => ['nullable', 'string', 'max:100'],
            'commentaire'   => ['nullable', 'string', 'max:255'],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'Le numéro du compte est obligatoire.',
            'numero.unique'   => 'Ce numéro de compte direction existe déjà.',
        ];
    }
}
