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

    public function showPurchaseOrderForm()
    {
        return view('test-purchaseorder-form');
    }

    public function createPurchaseOrder(Request $request)
    {
        try {
            $payload = $request->all();
            $result = $this->zohoService->createPurchaseOrder($payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create purchase order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getVendors()
    {
        try {
            $vendors = $this->zohoService->getVendors();
            return response()->json($vendors);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch vendors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function insufficientVendors(Request $request)
    {
        $itemIds = $request->input('item_ids', []);
        $result = [];
        foreach ($itemIds as $itemId) {
            $item = $this->zohoService->getItemById($itemId);
            $preferred = null;
            if (isset($item['item']['preferred_vendors']) && is_array($item['item']['preferred_vendors']) && count($item['item']['preferred_vendors']) > 0) {
                // We take the first one from is_primary or just the first one
                foreach ($item['item']['preferred_vendors'] as $vendor) {
                    if (!isset($preferred) || !empty($vendor['is_primary'])) {
                        $preferred = [
                            'vendor_id' => $vendor['vendor_id'],
                            'vendor_name' => $vendor['vendor_name']
                        ];
                        if (!empty($vendor['is_primary'])) break;
                    }
                }
            }
            $result[] = [
                'item_id' => $itemId,
                'preferred_vendor' => $preferred
            ];
        }
        return response()->json(['items' => $result]);
    }
}