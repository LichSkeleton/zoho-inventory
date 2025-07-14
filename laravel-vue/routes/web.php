<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoOAuthController;
use App\Http\Controllers\ZohoTestController;
use App\Services\ZohoInventoryService;

// OAuth routes
Route::get('/oauth/zoho', [ZohoOAuthController::class, 'redirectToZoho']);
Route::get('/oauth/zoho/callback', [ZohoOAuthController::class, 'handleZohoCallback']);

// Test routes
Route::prefix('test/zoho')->group(function () {
    
    // Items routes
    Route::get('/items', [ZohoTestController::class, 'showItems']);
    Route::get('/items/data', [ZohoTestController::class, 'getItems']);
    
    // Customers routes
    Route::get('/customers', [ZohoTestController::class, 'showCustomers']);
    Route::get('/customers/data', [ZohoTestController::class, 'getCustomers']);
    
    // Organizations routes
    Route::get('/organizations', [ZohoTestController::class, 'showOrganizations']);
    Route::get('/organizations/data', [ZohoTestController::class, 'getOrganizations']);
    
    // Sales order routes
    Route::get('/salesorder', [ZohoTestController::class, 'showSalesOrderForm']);
    Route::post('/salesorder', function (ZohoInventoryService $zohoService, \Illuminate\Http\Request $request) {
        try {
            $orderData = [
                'customer_name' => $request->input('customer_name'),
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
});