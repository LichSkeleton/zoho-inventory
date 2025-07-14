<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\ZohoTokenService;

class ZohoInventoryService
{
    protected $baseUrl = 'https://www.zohoapis.eu/inventory/v1/';
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

        // Add organization_id as query parameter for all requests
        $queryParams = ['organization_id' => $this->organizationId];
        
        // For GET requests, merge data with query params
        if ($method === 'GET' && !empty($data)) {
            $queryParams = array_merge($queryParams, $data);
            $data = [];
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ]);

        if ($method === 'POST') {
            $response = $response->post($url . '?' . http_build_query($queryParams), $data);
        } elseif ($method === 'PUT') {
            $response = $response->put($url . '?' . http_build_query($queryParams), $data);
        } else {
            $response = $response->get($url, $queryParams);
        }

        $result = $response->json();
        
        // Add debug information
        if (isset($result['code']) && $result['code'] !== 0) {
            \Log::error('Zoho API Error', [
                'url' => $url,
                'method' => $method,
                'response' => $result,
                'organization_id' => $this->organizationId
            ]);
        }

        return $result;
    }

    public function getItems()
    {
        return $this->request('items');
    }

    public function getCustomers()
    {
        return $this->request('contacts');
    }

    public function createCustomer(array $customerData)
    {
        return $this->request('contacts', 'POST', $customerData);
    }

    public function createSalesOrder(array $payload)
    {
        return $this->request('salesorders', 'POST', $payload);
    }

    public function createSalesOrderWithCustomer(array $orderData)
    {
        // First, try to find existing customer by email
        $customers = $this->getCustomers();
        $existingCustomer = null;
        
        if (isset($customers['contacts'])) {
            foreach ($customers['contacts'] as $customer) {
                if (mb_strtolower($customer['contact_name']) === mb_strtolower($orderData['customer_name'])) {
                    $existingCustomer = $customer;
                    break;
                }
            }
        }

        // If customer doesn't exist, create one
        if (!$existingCustomer) {
            $customerPayload = [
                'contact_name' => $orderData['customer_name'],
                'contact_type' => 'customer'
            ];

            $customerResponse = $this->createCustomer($customerPayload);
            
            if (isset($customerResponse['contact'])) {
                $existingCustomer = $customerResponse['contact'];
            } else {
                throw new \Exception('Failed to create customer: ' . json_encode($customerResponse));
            }
        }

        // Now create the sales order with the customer ID
        $salesOrderPayload = [
            'customer_id' => $existingCustomer['contact_id'],
            'line_items' => $orderData['line_items']
        ];

        return $this->createSalesOrder($salesOrderPayload);
    }

    public function getOrganizations()
    {
        // This endpoint can help verify your organization ID
        return $this->request('organizations');
    }
}