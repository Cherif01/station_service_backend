<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreCreanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /**
             * ============================
             * CLIENT
             * ============================
             */
            'id_client' => 'required|exists:clients,id',

            /**
             * ============================
             * CRÉANCES (TABLEAU)
             * ============================
             */
            'creances' => 'required|array|min:1',

            'creances.*.date' => 'nullable|date',

            'creances.*.quantite' => 'required|numeric|min:0.01',

            'creances.*.prix_unitaire' => 'required|numeric|min:0',

            // 🔹 recalculé côté backend
            'creances.*.montant' => 'nullable|numeric|min:0',

            'creances.*.commentaire' => 'nullable|string|max:255',
        ];
    }

    /**
     * ============================
     * VALIDATION MÉTIER AVANCÉE
     * ============================
     */
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->input('creances', []) as $index => $item) {

                $quantite = (float) ($item['quantite'] ?? 0);
                $prix     = (float) ($item['prix_unitaire'] ?? 0);

                if ($quantite <= 0) {
                    $validator->errors()->add(
                        "creances.$index.quantite",
                        'La quantité doit être supérieure à zéro.'
                    );
                }

                if ($prix < 0) {
                    $validator->errors()->add(
                        "creances.$index.prix_unitaire",
                        'Le prix unitaire ne peut pas être négatif.'
                    );
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 'error',
                'message' => 'Erreur de validation',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
