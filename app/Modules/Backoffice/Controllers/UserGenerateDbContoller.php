<?php

namespace App\Modules\Backoffice\Controllers;

use App\Events\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Models\Client;
use App\Modules\Backoffice\Models\Licence;
use App\Modules\Backoffice\Requests\ClientRequest;
use App\Services\SmsService;
use App\Traits\ApiResponses;
use App\Traits\DBconnection;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserGenerateDbContoller extends Controller
{
    use ApiResponses, DBconnection;

    public function generate(ClientRequest $request, SmsService $smsService)
    {
        try {
            DB::beginTransaction();

            /**
             * Vérifié le code soumis & Récupéré company
             */
            $request->validated();
            $client = Client::where('code', $request->code)->first();

            if (!$client) {
                DB::rollback();
                return $this->errorResponse("Ce code est invalide", 401);
            }

            /**
             * Vérifié si la base de données n'est pas déjà créer
             */
            if ($client->is_created) {
                DB::rollback();
                return $this->errorResponse("Cette base de données a déjà été créée", 422);
            }

            /**
             * Créer la base de données
             * Changé le status de la base de données
             * Connecté la base données
             * Et migré toutes les tables de la base données
             */
            DB::statement("CREATE DATABASE `$client->database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            $client->is_created = true;
            $client->is_active = true;
            $client->save();

            Licence::where('client_id', $client->id)->where('date_expiration', null)->update([
                'date_expiration' => now()->addDays(7),
                'days' => 7
            ]);

            $message = "Bonjour !\nVotre base de donnée a été créée avec succès.\nVous pouvez vous connecter maintenant en utilisant le code de votre entreprise et votre numéro de téléphone.\nMerci de nous avoir choisi.\nSPA Technology";
            SendMessageEvent::dispatch($client->telephone, $message);

            $this->connectToDatabase($client->database);

            Artisan::call('migrate', [
                '--database' => 'mysql',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            /**
             * Insérer le propriétaire de l'école dans la base données
             */
            DB::connection('mysql')->table('users')->insert([
                'name' => $client['name'],
                'telephone' => $client['telephone'],
                'adresse' => $client['adresse'] ?? null,
                'email' => $client['email'],
                'password' => Hash::make('123456'),
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => "La base de données a été généré avec succès"
            ], 200);
        } catch (AuthorizationException $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse("Échec de la création de la base de données: " . $e->getMessage(), 500);
        }
    }
}
