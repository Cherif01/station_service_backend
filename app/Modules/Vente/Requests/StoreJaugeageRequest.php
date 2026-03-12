<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreJaugeageRequest extends FormRequest
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
                'integer',
                'exists:cuves,id',
            ],

            'hauteur' => [
                'required',
                'numeric',
                'min:0',
            ],

            'volume_mesure' => [
                'required',
                'numeric',
                'min:0',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

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
