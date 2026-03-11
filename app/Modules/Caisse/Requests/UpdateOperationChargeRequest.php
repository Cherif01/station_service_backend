<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateOperationChargeRequest extends FormRequest
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
            'id_station' => 'sometimes|exists:stations,id',

            /**
             * ============================
             * COMPTE
             * ============================
             */
            'id_compte' => 'sometimes|exists:comptes,id',

            /**
             * ============================
             * CATÉGORIE CHARGE
             * ============================
             */
            'id_charge_category' => 'sometimes|exists:charge_categories,id',

            /**
             * ============================
             * DONNÉES
             * ============================
             */
            'date_operation' => 'nullable|date',

            'montant' => 'sometimes|numeric|min:0.01',

            'commentaire' => 'nullable|string|max:255',
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

            $montant = (float) $this->input('montant', 0);

            if ($this->has('montant') && $montant <= 0) {

                $validator->errors()->add(
                    "montant",
                    'Le montant doit être supérieur à zéro.'
                );
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