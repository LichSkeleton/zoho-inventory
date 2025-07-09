<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZohoTokenService
{
    protected $oauthUrl = 'https://accounts.zoho.eu/oauth/v2/token';

    public function getAccessToken(): string
    {
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        $refreshToken = Cache::get('zoho_refresh_token');
        
        if (!$refreshToken) {
            throw new \Exception('No refresh token available. Please re-authenticate.');
        }

        $response = Http::withoutVerifying()
            ->asForm()
            ->post($this->oauthUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'refresh_token' => $refreshToken,
            ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            Cache::put('zoho_access_token', $data['access_token'], now()->addSeconds($data['expires_in'] - 300)); // 5 minutes buffer
            return $data['access_token'];
        }

        \Log::error('Failed to refresh Zoho access token', [
            'response' => $data,
            'status' => $response->status()
        ]);

        throw new \Exception('Failed to refresh Zoho access token: ' . ($data['error'] ?? 'Unknown error'));
    }
}