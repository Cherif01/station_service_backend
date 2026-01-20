<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreProduit1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'       => 'required|string|max:150',
            'qte_actuelle'  => 'required|numeric|min:0',
            'prix_unitaire' => 'required|numeric|min:0',
            'seuil_alerte'  => 'nullable|numeric|min:0',
            'status'        => 'nullable|boolean',
        ];
    }

    /**
     * =================================================
     * 🔹 MESSAGES PERSONNALISÉS
     * =================================================
     */
    public function messages(): array
    {
        return [
            // Libellé
            'libelle.required' => 'Le libellé du produit est obligatoire.',
            'libelle.string'   => 'Le libellé du produit doit être un texte valide.',
            'libelle.max'      => 'Le libellé du produit ne doit pas dépasser 150 caractères.',

            // Quantité actuelle
            'qte_actuelle.required' => 'La quantité actuelle du produit est obligatoire.',
            'qte_actuelle.numeric'  => 'La quantité actuelle doit être un nombre.',
            'qte_actuelle.min'      => 'La quantité actuelle ne peut pas être négative.',

            // Prix unitaire
            'prix_unitaire.required' => 'Le prix unitaire du produit est obligatoire.',
            'prix_unitaire.numeric'  => 'Le prix unitaire doit être un nombre.',
            'prix_unitaire.min'      => 'Le prix unitaire ne peut pas être négatif.',

            // Seuil d’alerte
            'seuil_alerte.numeric' => 'Le seuil d’alerte doit être un nombre.',
            'seuil_alerte.min'     => 'Le seuil d’alerte ne peut pas être négatif.',

            // Statut
            'status.boolean' => 'Le statut du produit doit être vrai ou faux.',
        ];
    }

    protected function failedValidation(Validator $v)
    {
        throw new ValidationException(
            $v,
            response()->json([
                'status'  => 'error',
                'message' => 'Certaines informations sont invalides. Veuillez vérifier les champs saisis.',
                'errors'  => $v->errors(),
            ], 422)
        );
    }
}
