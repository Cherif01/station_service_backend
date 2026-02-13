<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreVenteLitreRequest extends FormRequest
{
    /**
     * =================================================
     * AUTORISATION
     * =================================================
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * =================================================
     * RÈGLES DE VALIDATION
     * =================================================
     */
    public function rules(): array
    {
        return [
            'id_cuve' => [
                'required',
                'exists:cuves,id',
            ],

            'qte_vendu' => [
                'required',
                'numeric',
                'min:0.001',
            ],
             'volume' => [
                'nullable',
                'numeric',
                'min:0.001',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * =================================================
     * FORMAT D’ERREUR PERSONNALISÉ
     * =================================================
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation des données envoyées.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
