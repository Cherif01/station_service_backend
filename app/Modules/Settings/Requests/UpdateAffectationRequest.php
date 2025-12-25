<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pompe'    => 'sometimes|exists:pompes,id',
            'id_pompiste' => 'sometimes|exists:users,id',
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
