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