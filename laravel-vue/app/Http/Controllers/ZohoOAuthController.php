<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class ZohoOAuthController extends Controller
{
    protected $authUrl = 'https://accounts.zoho.eu/oauth/v2/auth';
    protected $tokenUrl = 'https://accounts.zoho.eu/oauth/v2/token';

    public function redirectToZoho()
    {
        $clientId = config('services.zoho.client_id');
        $redirectUri = config('services.zoho.redirect_uri');
        $scope = 'ZohoInventory.fullaccess.all';
        $state = uniqid();

        $url = $this->authUrl . "?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => $scope,
            'redirect_uri' => $redirectUri,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ]);

        return redirect($url);
    }

    public function handleZohoCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json(['error' => 'Authorization code not received'], 400);
        }

        $response = Http::withoutVerifying()
            ->asForm()
            ->post($this->tokenUrl, [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.zoho.client_id'),
                'client_secret' => config('services.zoho.client_secret'),
                'redirect_uri' => config('services.zoho.redirect_uri'),
                'code' => $code,
            ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            Cache::put('zoho_access_token', $data['access_token'], now()->addSeconds($data['expires_in'] - 300)); // 5 minutes buffer
            
            // Store refresh token (only returned on first authorization)
            if (isset($data['refresh_token'])) {
                Cache::put('zoho_refresh_token', $data['refresh_token'], now()->addYears(1)); // Refresh tokens usually last longer
            }

            return response()->json([
                'success' => true,
                'message' => 'OAuth authorization successful',
                'expires_in' => $data['expires_in'],
                'scope' => $data['scope'] ?? null,
            ]);
        }

        \Log::error('OAuth authorization failed', [
            'response' => $data,
            'status' => $response->status()
        ]);

        return response()->json([
            'error' => 'OAuth authorization failed',
            'details' => $data
        ], 400);
    }
}