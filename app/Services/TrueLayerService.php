<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrueLayerService
{
    /**
     * Verify TrueLayer credentials by attempting to get an access token.
     */
    public function verifyCredentials(string $clientId, string $clientSecret): bool
    {
        // For production, this should be https://auth.truelayer.com/connect/token
        // For sandbox: https://auth.truelayer-sandbox.com/connect/token
        $url = 'https://auth.truelayer-sandbox.com/connect/token';

        $response = Http::asForm()->post($url, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            // Scope required for basic validation, 'info' or 'payments' depending on the app
            'scope' => 'info', 
        ]);

        return $response->successful() && isset($response->json()['access_token']);
    }
}
