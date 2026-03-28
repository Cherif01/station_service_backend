<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StorePompeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'        => 'required|string|max:150',
            'reference'      => 'nullable|string|max:50',
            'type_pompe'     => 'required|in:essence,gasoil,gazoil',
            'index_initial'  => 'nullable|numeric|min:0',
            'id_station'     => 'required|exists:stations,id',
            'status'         => 'nullable|boolean',
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
