<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersementDepuisDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_compte_direction' => ['required', 'integer', 'exists:comptes_direction,id'],
            'id_station'          => ['required', 'integer', 'exists:stations,id'],
            'montant'             => ['required', 'numeric', 'min:0.01'],
            'mode'                => ['required', 'in:banque,especes,virement,chéque'],
            'commentaire'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_compte_direction.required' => 'Le compte direction est obligatoire.',
            'id_compte_direction.exists'   => 'Compte direction introuvable.',
            'id_station.required'          => 'La station destinataire est obligatoire.',
            'id_station.exists'            => 'Station introuvable.',
            'montant.required'             => 'Le montant est obligatoire.',
            'montant.min'                  => 'Le montant doit être supérieur à 0.',
            'mode.required'                => 'Le mode de versement est obligatoire.',
            'mode.in'                      => 'Le mode doit être : banque, especes, virement ou chéque.',
        ];
    }
}
