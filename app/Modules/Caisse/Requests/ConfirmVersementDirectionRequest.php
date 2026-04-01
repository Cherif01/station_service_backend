<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmVersementDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required' => 'La référence du versement est obligatoire.',
        ];
    }
}
