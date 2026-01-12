<?php
namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Administration\Resources\UserResource;
use App\Modules\Settings\Models\Station;
use App\Notifications\Channels\NimbaSmsService;
use App\Traits\ImageUpload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    use ImageUpload;

    public function getCompanyCode(): ?string
    {
        $request = app(Request::class);

        $code = $request->header('code');

        return $code ?: null;
    }

    /**
     * ============================
     * Liste des utilisateurs
     * ============================
     */

    public function getAll()
    {
        try {

            $users = User::visible()
                ->with([
                    'station',      // dernière affectation → station courante
                    'affectations', // historique complet
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => UserResource::collection($users),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des utilisateurs.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function pompisteDisp()
    {
        try {

            $pompistes = User::visible()
                ->pompistesDisponibles()
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => UserResource::collection($pompistes),
            ]);

        } catch (\Throwable $e) {

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

// public function store(array $data)
// {
//     try {

//         // =================================================
//         // 🔹 Upload image si présente
//         // =================================================
//         if (! empty($data['image'])) {
//             $data['image'] = $this->imageUpload($data['image'], 'users');
//         }

//         // =================================================
//         // 🔹 Mot de passe
//         // =================================================
//         $plainPassword     = $data['password'] ?? '123456';
//         $data['password'] = Hash::make($plainPassword);

//         // =================================================
//         // 🔹 Création utilisateur
//         // =================================================
//         $user = User::create($data);

//         // Relations utiles
//         $user->load(['station', 'createdBy', 'modifiedBy']);

//         // =================================================
//         // 🔹 Récupération code entreprise (HEADER: code)
//         // =================================================
//         $companyCode = $this->getCompanyCode();

//         // =================================================
//         // 🔹 Préparation SMS
//         // =================================================
//         $smsEnvoye = false;

//         if (! empty($user->telephone)) {

//             // ✅ LOGIQUE SÉCURISÉE (celle que tu veux)
//             $stationName = 'votre station';

//             if (! empty($data['id_station'])) {
//                 $stationName = Station::where('id', $data['id_station'])
//                     ->value('libelle') ?? 'votre station';
//             }

//             $message =
//                 " Bienvenue {$user->name}.\n"
//                 . "Votre compte a été créé avec succès.\n"
//                 . "Entreprise : {$companyCode}\n"
//                 . "Station : {$stationName}\n"
//                 . "Téléphone : {$user->telephone}\n"
//                 . "Mot de passe : {$plainPassword}";

//             // =================================================
//             // 🔹 Envoi SMS (NON BLOQUANT)
//             // =================================================
//             try {

//                 $nimbaSms    = app(NimbaSmsService::class);
//                 $smsResponse = $nimbaSms->sendOtp($user->telephone, $message);

//                 if ($smsResponse instanceof \Illuminate\Http\JsonResponse) {
//                     $responseData = $smsResponse->getData(true);

//                     if (
//                         isset($responseData['success']) &&
//                         $responseData['success'] === true
//                     ) {
//                         $smsEnvoye = true;
//                     }
//                 }

//             } catch (\Throwable $e) {

//                 // ⚠️ Log uniquement — ne jamais bloquer la création utilisateur
//                 Log::warning(
//                     "Échec de l’envoi du SMS à {$user->telephone} : " . $e->getMessage()
//                 );
//             }
//         }

//         // =================================================
//         // 🔹 Réponse API
//         // =================================================
//         return response()->json([
//             'status'  => 200,
//             'message' => 'Utilisateur créé avec succès.',
//             'sms'     => $smsEnvoye ? 'envoyé' : 'non envoyé',
//             'data'    => new UserResource($user),
//         ], 200);

//     } catch (\Throwable $e) {

//         return response()->json([
//             'status'  => 500,
//             'message' => 'Erreur lors de la création de l’utilisateur.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }
    // public function store(array $data)
    // {
    //     try {

    //         // =================================================
    //         // 🔹 Upload image si présente
    //         // =================================================
    //         if (! empty($data['image'])) {
    //             $data['image'] = $this->imageUpload($data['image'], 'users');
    //         }

    //         // =================================================
    //         // 🔹 Mot de passe
    //         // =================================================
    //         $plainPassword    = $data['password'] ?? '123456';
    //         $data['password'] = Hash::make($plainPassword);

    //         // =================================================
    //         // 🔹 Création utilisateur
    //         // =================================================
    //         $user = User::create($data);

    //         $user->load(['station', 'createdBy', 'modifiedBy']);

    //         // =================================================
    //         // 🔹 Code entreprise (HEADER: code)
    //         // =================================================
    //         $companyCode = $this->getCompanyCode();

    //         // =================================================
    //         // 🔹 SMS
    //         // =================================================
    //         $smsEnvoye = false;

    //         if (! empty($user->telephone)) {

    //             $stationName = 'votre station';

    //             if (! empty($data['id_station'])) {
    //                 $stationName = Station::where('id', $data['id_station'])
    //                     ->value('libelle') ?? 'votre station';
    //             }

    //             $stationUrl = 'https://station.spa-dev.com/';

    //             $message =
    //                 "Bienvenue {$user->name}.\n"
    //                 . "Votre compte a été créé avec succès.\n"
    //                 . "Entreprise : {$companyCode}\n"
    //                 . "Station : {$stationName}\n"
    //                 . "Téléphone : {$user->telephone}\n"
    //                 . "Mot de passe : {$plainPassword}\n"
    //                 . "Accès : {$stationUrl}";

    //             try {

    //                 $nimbaSms    = app(NimbaSmsService::class);
    //                 $smsResponse = $nimbaSms->sendOtp($user->telephone, $message);

    //                 if ($smsResponse instanceof \Illuminate\Http\JsonResponse) {
    //                     $responseData = $smsResponse->getData(true);

    //                     if (
    //                         isset($responseData['success']) &&
    //                         $responseData['success'] === true
    //                     ) {
    //                         $smsEnvoye = true;
    //                     }
    //                 }

    //             } catch (\Throwable $e) {
    //                 Log::warning(
    //                     "Échec de l’envoi du SMS à {$user->telephone} : " . $e->getMessage()
    //                 );
    //             }
    //         }

    //         // =================================================
    //         // 🔹 Réponse API
    //         // =================================================
    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Utilisateur créé avec succès.',
    //             'sms'     => $smsEnvoye ? 'envoyé' : 'non envoyé',
    //             'data'    => new UserResource($user),
    //         ], 200);

    //     } catch (\Throwable $e) {

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de l’utilisateur.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function store(array $data)
    {
        try {

            // =================================================
            // 🔹 Upload image
            // =================================================
            if (! empty($data['image'])) {
                $data['image'] = $this->imageUpload($data['image'], 'users');
            }

            // =================================================
            // 🔹 Mot de passe
            // =================================================
            $plainPassword    = $data['password'] ?? '123456';
            $data['password'] = Hash::make($plainPassword);

            // =================================================
            // 🔹 Création utilisateur
            // =================================================
            $user = User::create($data);
            $user->load(['station', 'createdBy', 'modifiedBy']);

            // =================================================
            // 🔹 Code entreprise
            // =================================================
            $companyCode = $this->getCompanyCode();

            // =================================================
            // 🔹 SMS
            // =================================================
            $smsEnvoye = false;
            $smsErreur = null;

            if (! empty($user->telephone)) {

                $stationName = Station::where('id', $data['id_station'] ?? null)
                    ->value('libelle') ?? 'votre station';

                $message =
                    "Bonjour {$user->name},\n"
                    . "Votre compte personnel a été créé avec succès.\n"
                    . "Code : {$companyCode}\n"
                    . "Station : {$stationName}\n"
                    . "Identifiant : {$user->telephone}\n"
                    . "Mot de passe : {$plainPassword}\n"
                    . "Accès : https://station.spa-dev.com/";

                $nimbaSms  = app(NimbaSmsService::class);
                $smsResult = $nimbaSms->sendOtp($user->telephone, $message);

                $smsEnvoye = $smsResult['success'] ?? false;
                $smsErreur = $smsResult['message'] ?? null;
            }

            // =================================================
            // 🔹 Réponse API
            // =================================================
            return response()->json([
                'status'   => 200,
                'message'  => 'Utilisateur créé avec succès.',
                'sms'      => $smsEnvoye ? 'envoyé' : 'non envoyé',
                'sms_info' => $smsErreur,
                'data'     => new UserResource($user),
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’utilisateur.',
                'error'   => $e->getMessage(),
            ], 500);
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

            $user = User::visible()
                ->with([
                    'station',      // station courante (dernière affectation)
                    'affectations', // historique
                    'createdBy',
                    'modifiedBy',
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new UserResource($user),
            ]);

        } catch (\Throwable $e) {

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
