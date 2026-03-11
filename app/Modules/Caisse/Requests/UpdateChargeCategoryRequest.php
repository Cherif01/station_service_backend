<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateChargeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'libelle' => 'sometimes|string|max:150',

            'is_fixed' => 'sometimes|boolean',

            'status' => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}