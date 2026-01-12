<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NimbaSmsService
{
    public function sendOtp(string $telephone, string $message): array
    {
        try {
            $serviceId   = config('services.nimba.service_id');
            $secretToken = config('services.nimba.secret_token');
            $basicToken  = config('services.nimba.basic_token');
            $sender      = config('services.nimba.sender');
            $url         = config('services.nimba.url');

            if (! $serviceId || ! $secretToken || ! $sender || ! $url || ! $basicToken) {
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

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'message'  => 'SMS envoyé avec succès.',
                    'provider' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Échec envoi SMS.',
                'error'   => $response->json(),
                'status'  => $response->status(),
            ];

        } catch (\Throwable $e) {

            Log::error('Erreur SMS Nimba : ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception lors de l’envoi SMS.',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
