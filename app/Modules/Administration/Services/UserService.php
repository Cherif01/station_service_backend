<?php
namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Administration\Resources\UserResource;
use App\Traits\ImageUpload;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use ImageUpload;

    /**
     * ============================
     * Liste des utilisateurs
     * ============================
     */

    public function getAll()
    {
        try {

            // 🔹 Requête simple
            $users = User::visible()
                ->with(['station', 'createdBy', 'modifiedBy'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => UserResource::collection($users),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des utilisateurs.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Création utilisateur
     * ============================
     */
    public function store(array $data)
    {
        try {

            // 🔹 Upload image si présente
            if (! empty($data['image'])) {
                $data['image'] = $this->imageUpload($data['image'], 'users');
            }

            // 🔹 Mot de passe
            // - si fourni → hash
            // - sinon → mot de passe par défaut "123456"
            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                $data['password'] = Hash::make('123456');
            }

            $user = User::create($data);
            $user->load(['station', 'createdBy', 'modifiedBy']);

            return response()->json([
                'status'  => 200,
                'message' => 'Utilisateur créé avec succès.',
                'data'    => new UserResource($user),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’utilisateur.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Mise à jour utilisateur
     * ============================
     */
    public function update(int $id, array $data)
    {
        try {

            $user = User::findOrFail($id);

            // Upload nouvelle image
            if (! empty($data['image'])) {

                if ($user->image) {
                    $this->deleteImage($user->image, 'users');
                }

                $data['image'] = $this->imageUpload($data['image'], 'users');
            }

            // Hash mot de passe uniquement si fourni et non vide
            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            $user->load(['station', 'createdBy', 'modifiedBy']);

            return response()->json([
                'status'  => 200,
                'message' => 'Utilisateur mis à jour avec succès.',
                'data'    => new UserResource($user),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de l’utilisateur.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Suppression utilisateur
     * ============================
     */
    public function delete(int $id)
    {
        try {

            $user = User::findOrFail($id);

            if ($user->image) {
                $this->deleteImage($user->image, 'users');
            }

            $user->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Utilisateur supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’utilisateur.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Détail utilisateur
     * ============================
     */
    public function getOne(int $id)
    {
        try {

            $user = User::with(['station', 'createdBy', 'modifiedBy'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new UserResource($user),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Utilisateur introuvable.',
            ]);
        }
    }

    /**
     * ============================
     * Connexion (login)
     * ============================
     */
    public function login(array $data)
    {
        try {

            // ⚠️ IMPORTANT : désactiver les Global Scopes pour le login
            $user = User::withoutGlobalScopes()
                ->with(['station', 'createdBy', 'modifiedBy'])
                ->where('telephone', $data['telephone'])
                ->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'status'  => 401,
                    'message' => 'Téléphone ou mot de passe incorrect.',
                ]);
            }

            if (! $user->status) {
                return response()->json([
                    'status'  => 403,
                    'message' => 'Votre compte est désactivé. Veuillez contacter l’administration.',
                ]);
            }

            $token = $user->createToken('station-auth')->plainTextToken;

            return response()->json([
                'status'  => 200,
                'message' => 'Connexion réussie.',
                'token'   => $token,
                'data'    => new UserResource($user),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la connexion.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

}
