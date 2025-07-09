<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZohoTokenService
{
    public function getAccessToken(): string
    {
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        $refreshToken = Cache::get('zoho_refresh_token');

        // Fix: Add withoutVerifying() to disable SSL verification for local development
        $response = Http::withoutVerifying()
            ->asForm()
            ->post(config('services.zoho.oauth_url'), [
                'grant_type' => 'refresh_token',
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'refresh_token' => $refreshToken,
            ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            Cache::put('zoho_access_token', $data['access_token'], now()->addSeconds($data['expires_in']));
            return $data['access_token'];
        }

        throw new \Exception('Failed to refresh Zoho access token.');
    }
}