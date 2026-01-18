<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreRetourCuveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cuve' => [
                'required',
                'exists:cuves,id',
            ],

            'qte_appro' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:255',
            ],
             'pu_unitaire'  => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation du retour de cuve.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
