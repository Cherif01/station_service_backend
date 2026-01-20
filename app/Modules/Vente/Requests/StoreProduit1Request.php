<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreProduit1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'       => 'required|string|max:150',
            'qte_initiale'  => 'required|numeric|min:0',
            'qte_actuelle'  => 'required|numeric|min:0',
            'prix_unitaire' => 'required|numeric|min:0',
            'seuil_alerte'  => 'nullable|numeric|min:0',
            'status'        => 'nullable|boolean',
        ];
    }

    protected function failedValidation(Validator $v)
    {
        throw new ValidationException($v, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation',
            'errors'  => $v->errors(),
        ], 422));
    }
}
