<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreTransfertCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_source'      => ['required', 'exists:comptes,id'],
            'id_destination' => ['required', 'exists:comptes,id', 'different:id_source'],
            'montant'               => ['required', 'numeric', 'gt:0'],
            'reference'             => ['nullable', 'string', 'max:100'],
            'commentaire'           => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation du transfert.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
