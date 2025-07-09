<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\ZohoTokenService;

class ZohoInventoryService
{
    protected $baseUrl = 'https://inventory.zoho.eu/api/v1/';
    protected $organizationId;
    protected $accessToken;

    public function __construct(ZohoTokenService $tokenService)
    {
        $this->organizationId = config('services.zoho.organization_id');
        $this->accessToken = $tokenService->getAccessToken();
    }

    protected function request($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->baseUrl . $endpoint;

        // Fix: Add withoutVerifying() to disable SSL verification for local development
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken,
                'X-com-zoho-inventory-organizationid' => $this->organizationId,
            ]);

        if ($method === 'POST') {
            $response = $response->post($url, $data);
        } elseif ($method === 'PUT') {
            $response = $response->put($url, $data);
        } else {
            $response = $response->get($url, $data);
        }

        return $response->json();
    }

    public function getItems()
    {
        return $this->request('items');
    }

    public function createSalesOrder(array $payload)
    {
        return $this->request('salesorders', 'POST', $payload);
    }
}