<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NimbaSmsService
{
    public function sendOtp(string $telephone, string $message): array
    {
        try {
            // =================================================
            // 🔹 Configuration (services.php)
            // =================================================
            $serviceId   = config('services.nimba.service_id');
            $secretToken = config('services.nimba.secret'); // ✅ cohérent avec services.php
            $basicToken  = config('services.nimba.basic_token');
            $sender      = config('services.nimba.sender');
            $url         = config('services.nimba.url');

            // =================================================
            // 🔹 Vérification config
            // =================================================
            if (! $serviceId || ! $secretToken || ! $basicToken || ! $sender || ! $url) {
                return [
                    'success' => false,
                    'message' => 'Configuration SMS Nimba incomplète.',
                ];
            }

            if (strlen($sender) > 11) {
                return [
                    'success' => false,
                    'message' => 'Le sender_name dépasse 11 caractères.',
                ];
            }

            // =================================================
            // 🔹 Envoi SMS
            // =================================================
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $basicToken,
                'Accept'        => 'application/json',
            ])
            ->timeout(15)
            ->post($url, [
                'sender_name' => $sender,
                'to'          => [$telephone],
                'message'     => $message,
            ]);

            // =================================================
            // ✅ Succès
            // =================================================
            if ($response->successful()) {
                return [
                    'success'  => true,
                    'message'  => 'SMS envoyé avec succès.',
                    'provider' => $response->json(),
                ];
            }

            // =================================================
            // ❌ Erreur fournisseur (LOG AJOUTÉ ICI)
            // =================================================
            Log::error('NIMBA SMS ERROR', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'json'   => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Échec lors de l’envoi du SMS.',
                'status'  => $response->status(),
                'error'   => $response->json(),
            ];

        } catch (\Throwable $e) {

            Log::error('Erreur SMS Nimba : ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception lors de l’envoi du SMS.',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
