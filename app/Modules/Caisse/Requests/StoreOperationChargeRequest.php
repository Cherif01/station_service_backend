<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreOperationChargeRequest extends FormRequest
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
             * STATION
             * ============================
             */
          

            /**
             * ============================
             * COMPTE
             * ============================
             */
            'id_compte' => 'required|exists:comptes,id',

            /**
             * ============================
             * CATÉGORIE CHARGE
             * ============================
             */
            'id_charge_category' => 'required|exists:charge_categories,id',

            /**
             * ============================
             * OPÉRATIONS CHARGES (TABLEAU)
             * ============================
             */
            'operations' => 'required|array|min:1',

            'operations.*.date_operation' => 'nullable|date',

            'operations.*.montant' => 'required|numeric|min:0.01',

            'operations.*.commentaire' => 'nullable|string|max:255',
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

            foreach ($this->input('operations', []) as $index => $item) {

                $montant = (float) ($item['montant'] ?? 0);

                if ($montant <= 0) {

                    $validator->errors()->add(
                        "operations.$index.montant",
                        'Le montant doit être supérieur à zéro.'
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