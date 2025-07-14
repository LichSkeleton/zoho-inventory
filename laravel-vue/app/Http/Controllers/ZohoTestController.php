<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoInventoryService;

class ZohoTestController extends Controller
{
    protected $zohoService;

    public function __construct(ZohoInventoryService $zohoService)
    {
        $this->zohoService = $zohoService;
    }

    public function getItems()
    {
        try {
            $items = $this->zohoService->getItems();
            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showItems()
    {
        return view('zoho-items');
    }

    public function getCustomers()
    {
        try {
            $customers = $this->zohoService->getCustomers();
            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch customers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showCustomers()
    {
        return view('zoho-customers');
    }

    public function getOrganizations()
    {
        try {
            $organizations = $this->zohoService->getOrganizations();
            return response()->json($organizations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch organizations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showOrganizations()
    {
        return view('zoho-organizations');
    }

    public function showSalesOrderForm()
    {
        return view('test-salesorder-form');
    }

    public function createSalesOrder(Request $request)
    {
        try {
            $payload = $request->all();
            $result = $this->zohoService->createSalesOrder($payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create sales order',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}