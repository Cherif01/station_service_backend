<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * =========================
             * 🔹 CLÉ
             * =========================
             */
            'cle' => 'required|string|max:100',

            /**
             * =========================
             * 🔹 VALEUR
             * =========================
             */
            'valeur' => 'required|string',

            /**
             * =========================
             * 🔹 DESCRIPTION
             * =========================
             */
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cle.required' => 'La clé du paramètre est obligatoire.',
            'cle.string'   => 'La clé du paramètre doit être une chaîne de caractères.',
            'cle.max'      => 'La clé du paramètre ne doit pas dépasser 100 caractères.',
            'valeur.string' => 'La valeur du paramètre doit être une chaîne de caractères.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 'error',
                'message' => 'Erreur de validation lors de la création du paramètre.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
