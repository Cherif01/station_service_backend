<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompteDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('compte_direction');

        return [
            'numero'        => ['sometimes', 'string', 'max:50', 'unique:comptes_direction,numero,' . $id],
            'libelle'       => ['nullable', 'string', 'max:100'],
            'commentaire'   => ['nullable', 'string', 'max:255'],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
