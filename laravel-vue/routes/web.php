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

            // Checking balances (залишки)
            $items = $zohoService->getItems();
            $itemsById = [];
            if (isset($items['items'])) {
                foreach ($items['items'] as $item) {
                    $itemsById[$item['item_id']] = $item;
                }
            }
            $insufficient = [];
            foreach ($orderData['line_items'] as $line) {
                $itemId = $line['item_id'];
                $qty = $line['quantity'];
                $itemData = $itemsById[$itemId] ?? null;
                // Ignore service
                if ($itemData && (isset($itemData['product_type']) && $itemData['product_type'] === 'service' || isset($itemData['item_type']) && $itemData['item_type'] === 'service')) {
                    continue;
                }
                $stock = isset($itemData['actual_available_stock']) ? $itemData['actual_available_stock'] : 0;
                if ($stock < $qty) {
                    $insufficient[] = [
                        'item_id' => $itemId,
                        'name' => $itemData['name'] ?? 'Unknown',
                        'needed' => $qty - $stock,
                        'ordered' => $qty,
                        'in_stock' => $stock
                    ];
                }
            }
            if (count($insufficient) > 0) {
                return response()->json([
                    'status' => 'insufficient',
                    'insufficient_items' => $insufficient
                ]);
            }

            $result = $zohoService->createSalesOrderWithCustomer($orderData);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create sales order',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // Sales order without balance check (force create)
    Route::post('/salesorder/force', function (ZohoInventoryService $zohoService, \Illuminate\Http\Request $request) {
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

    // Purchase order routes
    Route::get('/purchaseorder', [App\Http\Controllers\ZohoTestController::class, 'showPurchaseOrderForm']);
    Route::post('/purchaseorder', [App\Http\Controllers\ZohoTestController::class, 'createPurchaseOrder']);
    // Vendors route
    Route::get('/vendors/data', [App\Http\Controllers\ZohoTestController::class, 'getVendors']);

    // Purchase order for insufficient items
    Route::post('/purchaseorder/insufficient', function (ZohoInventoryService $zohoService, \Illuminate\Http\Request $request) {
        $items = $request->input('insufficient_items', []);
        $vendor_id = $request->input('vendor_id');
        if (empty($items)) {
            return response()->json(['error' => 'No items provided'], 400);
        }
        // Get all products for rate substitution
        $allItems = $zohoService->getItems();
        $itemsById = [];
        if (isset($allItems['items'])) {
            foreach ($allItems['items'] as $it) {
                $itemsById[$it['item_id']] = $it;
            }
        }
        $line_items = [];
        foreach ($items as $item) {
            $rate = isset($item['rate']) && $item['rate'] > 0 ? $item['rate'] : null;
            if (!$rate && isset($itemsById[$item['item_id']])) {
                $rate = $itemsById[$item['item_id']]['purchase_rate'] ?? $itemsById[$item['item_id']]['rate'] ?? 0;
            }
            $line_items[] = [
                'item_id' => $item['item_id'],
                'quantity' => $item['needed'],
                'rate' => $rate
            ];
        }
        $payload = [
            'vendor_id' => $vendor_id,
            'line_items' => $line_items
        ];
        $result = $zohoService->createPurchaseOrder($payload);
        return response()->json($result);
    });
});