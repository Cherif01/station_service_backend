<?php

namespace App\Modules\Backoffice\Controllers;

use App\Events\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Models\Client;
use App\Modules\Backoffice\Models\Licence;
use App\Modules\Backoffice\Requests\ClientSignupRequest;
use App\Traits\ApiResponses;
use App\Traits\GenerateClientCode;
use App\Traits\GenerateLicence;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    use GenerateClientCode, GenerateLicence, ApiResponses;

    public function signup(ClientSignupRequest $request)
    {
        try {
            DB::beginTransaction();

            // Validation des données
            $fields = $request->validated();

            // Generation du nom de la base de donnée et du code de l'entreprise
            $database = 'spatech_station_' . Str::slug($fields['name']) . '_' . Str::random(10);
            $client_code = $this->getClientCode($fields['name']);

            // Stocké les données du client dans la table client
            $client = Client::create([
                'code' => $client_code,
                'name' => $fields['name'],
                'telephone' => $fields['telephone'],
                'adresse' => $fields['adresse'],
                'email' => $fields['email'],
                'is_created' => false,
                'is_active' => false,
                'database' => $database
            ]);

            Licence::create([
                'code_licence' => $this->getLicence(),
                'date_achat' => now()->format('Y-m-d'),
                'date_expiration' => null,
                'days' => 14,
                'is_available' => false,
                'is_sent' => true,
                'client_id' => $client->id,
                'created_by' => null,
            ]);

            // Envoi du code de l'ecole au client par notification email
            $message = "Bonjour !\nNous avons bien reçu votre inscription. Merci de patienter moins de 24h pendant que notre équipe prépare l’installation de votre entreprise en ligne.\n\nSPA TECHNOLOGY";

            SendMessageEvent::dispatch($client->telephone, $message);

            DB::commit();

            return $this->successResponse($client, "Le client a été créé et le code envoyé avec succès");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse("Échec de la création du client: " . $e->getMessage(), 500);
        }
    }
}
