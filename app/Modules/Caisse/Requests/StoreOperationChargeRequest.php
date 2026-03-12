<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreOperationChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_compte'          => 'required|exists:comptes,id',
            'id_charge_category' => 'required|exists:charge_categories,id',
            'montant'            => 'required|numeric|min:0.01',
            'commentaire'        => 'nullable|string|max:255',
            'status'             => 'nullable|boolean',
        ];
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
