<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoOAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/oauth/zoho', [ZohoOAuthController::class, 'redirectToZoho']);
Route::get('/oauth/zoho/callback', [ZohoOAuthController::class, 'handleZohoCallback']);

// Test routes for Zoho Inventory API
Route::get('/test/zoho/items', function (ZohoInventoryService $zohoService) {
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

Route::post('/test/zoho/salesorder', function (ZohoInventoryService $zohoService) {
    try {
        // Example sales order payload
        $payload = [
            'customer_id' => 'your_customer_id', // Replace with actual customer ID
            'line_items' => [
                [
                    'item_id' => 'your_item_id', // Replace with actual item ID
                    'quantity' => 1,
                    'rate' => 100.00
                ]
            ]
        ];

        $salesOrder = $zohoService->createSalesOrder($payload);
        return response()->json($salesOrder);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to create sales order',
            'message' => $e->getMessage()
        ], 500);
    }
});
