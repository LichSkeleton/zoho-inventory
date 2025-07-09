<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoOAuthController;
use App\Services\ZohoInventoryService;

// OAuth routes
Route::get('/oauth/zoho', [ZohoOAuthController::class, 'redirectToZoho']);
Route::get('/oauth/zoho/callback', [ZohoOAuthController::class, 'handleZohoCallback']);

// Test routes
Route::prefix('test/zoho')->group(function () {
    
    // GET route to test organizations (to verify organization ID)
    Route::get('/organizations', function (ZohoInventoryService $zohoService) {
        try {
            $organizations = $zohoService->getOrganizations();
            return response()->json($organizations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch organizations',
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    // GET route for items
    Route::get('/items', function (ZohoInventoryService $zohoService) {
        try {
            $items = $zohoService->getItems();
            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch items',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // GET route to test customers
    Route::get('/customers', function (ZohoInventoryService $zohoService) {
        try {
            $customers = $zohoService->getCustomers();
            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch customers',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // POST route for creating sales order
    Route::post('/salesorder', function (ZohoInventoryService $zohoService, \Illuminate\Http\Request $request) {
        try {
            $orderData = [
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'line_items' => $request->input('line_items', [])
            ];
            
            $result = $zohoService->createSalesOrderWithCustomer($orderData);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create sales order',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // GET route to show form for creating sales order (for testing)
    Route::get('/salesorder', function () {
        return view('test-salesorder-form');
    });
});