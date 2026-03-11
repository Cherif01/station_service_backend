<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'  => 'required|string|max:255',
            'pu_vente' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required'  => 'Le libellé est obligatoire.',
            'libelle.string'    => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max'       => 'Le libellé ne doit pas dépasser 255 caractères.',

            'pu_vente.required' => 'Le prix de vente est obligatoire.',
            'pu_vente.numeric'  => 'Le prix de vente doit être un nombre.',
            'pu_vente.min'      => 'Le prix de vente doit être supérieur ou égal à 0.',
        ];
    }
}