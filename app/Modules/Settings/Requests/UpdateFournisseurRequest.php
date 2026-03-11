<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [

            'raison_sociale' => 'sometimes|string|max:255',

            'nom_complet' => 'nullable|string|max:255',

            'telephone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('fournisseurs','telephone')->ignore($id)
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('fournisseurs','email')->ignore($id)
            ],

            'adresse' => 'nullable|string|max:255',

            'status' => 'sometimes|boolean',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $telephone = $this->input('telephone');

            if ($telephone && ! preg_match('/^[0-9]{8,15}$/', $telephone)) {

                $validator->errors()->add(
                    'telephone',
                    'Le numéro de téléphone doit contenir uniquement des chiffres.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 'error',
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}