<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersementDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_source'           => ['required', 'integer', 'exists:comptes,id'],
            'id_compte_direction' => ['required', 'integer', 'exists:comptes_direction,id'],
            'montant'             => ['required', 'numeric', 'min:0.01'],
            'mode'                => ['required', 'in:banque,especes,virement,chéque'],
            'commentaire'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_source.required'           => 'Le compte source est obligatoire.',
            'id_source.exists'             => 'Compte source introuvable.',
            'id_compte_direction.required' => 'Le compte direction est obligatoire.',
            'id_compte_direction.exists'   => 'Compte direction introuvable.',
            'montant.required'             => 'Le montant est obligatoire.',
            'montant.min'                  => 'Le montant doit être supérieur à 0.',
            'mode.required'                => 'Le mode de versement est obligatoire.',
            'mode.in'                      => 'Le mode doit être : banque, especes, virement ou chéque.',
        ];
    }
}
