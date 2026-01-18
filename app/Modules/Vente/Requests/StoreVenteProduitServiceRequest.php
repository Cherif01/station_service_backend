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
            // client obligatoire (id_init_vente sera généré côté service)
            'id_client' => 'required|exists:clients,id',

            /**
             * ============================
             * PRODUITS (TABLEAU D’OBJETS)
             * ============================
             */
            'ids_produits' => 'nullable|array',

            'ids_produits.*.id' => 'required|exists:produits,id',
            'ids_produits.*.qte_vendu'  => 'required|numeric|min:0.01',

            /**
             * ============================
             * SERVICES (TABLEAU D’IDS)
             * ============================
             */
            'ids_services'   => 'nullable|array',
            'ids_services.*' => 'required|exists:services,id',
        ];
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
