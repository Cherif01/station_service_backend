<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class LigneVenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // =========================
            // 🔹 Relations (optionnelles)
            // =========================
            'id_station'     => ['nullable', 'exists:stations,id'],
            'id_cuve'        => ['nullable', 'exists:produits,id'],
            'id_affectation' => ['nullable', 'exists:affectations,id'],

            // =========================
            // 🔹 Données de vente
            // (contrôle métier dans le service)
            // =========================
            'index_debut'    => ['nullable', 'numeric'],
            'index_fin'      => ['nullable', 'numeric'],
            'qte_vendu'      => ['nullable', 'numeric'],
            'retour_cuve'      => ['nullable', 'numeric'],
            'commentaire'      => ['nullable', 'string'],
        ];
    }

    /**
     * Messages personnalisés (facultatif)
     */
    public function messages(): array
    {
        return [
            'index_debut.numeric' => 'L’index de début doit être numérique.',
            'index_fin.numeric'   => 'L’index de fin doit être numérique.',
            'qte_vendu.numeric'   => 'La quantité vendue doit être numérique.',
        ];
    }

    /**
     * Réponse JSON normalisée en cas d’erreur de validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation des données envoyées.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
