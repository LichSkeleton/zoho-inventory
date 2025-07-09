<?php

namespace App\Services;

use GuzzleHttp\Client;

class ZohoOAuthService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.zoho.oauth_url'),
            'verify' => base_path('certs/cacert.pem'), // 👈 use your custom cert here!
        ]);
    }

    public function exchangeCodeForAccessToken(string $authCode)
    {
        $response = $this->client->post('', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'redirect_uri' => config('services.zoho.redirect_uri'),
                'code' => $authCode,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
