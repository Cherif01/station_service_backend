<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            
            'nom_complet' => 'required|string|max:150',
            'telephone'   => 'required|string|max:20|unique:clients,telephone',
            'email'       => 'nullable|email|unique:clients,email',
            'adresse'     => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
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
