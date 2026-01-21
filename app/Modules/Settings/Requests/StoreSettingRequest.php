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
             * 🔹 SETTINGS (clé => valeur)
             * =========================
             */
            'settings' => 'required|array',

            'settings.*' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Les paramètres sont obligatoires.',
            'settings.array'    => 'Les paramètres doivent être un tableau clé/valeur.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 'error',
                'message' => 'Erreur de validation des paramètres.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
