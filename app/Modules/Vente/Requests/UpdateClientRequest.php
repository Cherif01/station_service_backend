<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nom_complet' => 'sometimes|string|max:150',
            'telephone'   => "sometimes|string|max:20|unique:clients,telephone,$id",
            'email'       => "sometimes|nullable|email|unique:clients,email,$id",
            'adresse'     => 'sometimes|nullable|string|max:255',
            'status'      => 'sometimes|boolean',
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
