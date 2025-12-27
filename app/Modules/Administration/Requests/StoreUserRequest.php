<?php
namespace App\Modules\Administration\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Identité
            'name'       => 'required|string|max:100',

            // Auth
            'email'      => 'nullable|email|max:100|unique:users,email',
            'password'   => 'nullable|string|min:6',

            // Coordonnées
            'telephone'  => 'nullable|string|max:30',
            'adresse'    => 'nullable|string|max:150',

            // Image utilisateur
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Métier
            'role'       => 'required|in:super_admin,admin,gerant,superviseur,pompiste',
            'id_station' => 'nullable|exists:stations,id',
            'id_ville'   => 'nullable|exists:villes,id',

            'status'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',

            'email.email'       => 'L’email est invalide.',
            'email.unique'      => 'Cet email existe déjà.',

            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',

            'image.image'       => 'Le fichier doit être une image.',
            'image.mimes'       => 'L’image doit être au format jpg, jpeg, png ou webp.',
            'image.max'         => 'La taille maximale de l’image est de 2 Mo.',

            'role.required'     => 'Le rôle est obligatoire.',
            'role.in'           => 'Le rôle sélectionné est invalide.',

            'id_station.exists' => 'La station sélectionnée est invalide.',

            'status.boolean'    => 'Le statut doit être vrai ou faux.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
