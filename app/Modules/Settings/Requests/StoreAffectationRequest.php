<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_user'     => 'required|exists:users,id',
            'id_station'  => 'required|exists:stations,id',
            'id_pompe'    => 'nullable|exists:pompes,id',
            'index_debut' => 'nullable|numeric|min:0',
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
