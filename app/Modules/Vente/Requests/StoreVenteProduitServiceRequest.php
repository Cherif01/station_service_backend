<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreVenteProduitServiceRequest extends FormRequest
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
             * PRODUITS
             * ============================
             */
            'ids_produits' => 'nullable|array',
            'ids_produits.*.id' => 'required_with:ids_produits|exists:produits,id',
            'ids_produits.*.qte_vendu' => 'required_with:ids_produits|numeric|min:0.01',

            /**
             * ============================
             * SERVICES
             * ============================
             */
            'ids_services' => 'nullable|array',
            'ids_services.*' => 'required_with:ids_services|exists:services,id',
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

            $produits = $this->input('ids_produits', []);
            $services = $this->input('ids_services', []);

            if (empty($produits) && empty($services)) {
                $validator->errors()->add(
                    'ids_produits',
                    'Au moins un produit ou un service est requis pour créer une vente.'
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
