<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateProduit1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'       => 'sometimes|string|max:150',
            'qte_actuelle'  => 'sometimes|numeric|min:0',
            'prix_unitaire' => 'sometimes|numeric|min:0',
            'seuil_alerte'  => 'sometimes|nullable|numeric|min:0',
            'status'        => 'sometimes|boolean',
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
