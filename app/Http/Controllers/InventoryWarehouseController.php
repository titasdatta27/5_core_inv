<?php

namespace App\Http\Controllers;

use App\Models\InventoryWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class InventoryWarehouseController extends Controller
{

    public function index()
    {
        $warehouses = InventoryWarehouse::latest()->get();

        return view('purchase-master.transit_container.inventory_warehouse', compact('warehouses'));
    }

    // public function pushInventory(Request $request)
    // {
    //     $tabName = $request->input('tab_name');
    //     $rows = $request->input('data', []);

    //     foreach ($rows as $row) {
    //         InventoryWarehouse::create([
    //             'tab_name'          => $row['tab_name'] ?? $tabName,
    //             'supplier_name'     => $row['supplier_name'] ?? null,
    //             'company_name'      => $row['company_name'] ?? null,
    //             'our_sku'           => $row['our_sku'] ?? null,
    //             'parent'            => $row['parent'] ?? null,
    //             'no_of_units'       => !empty($row['no_of_units']) ? (int) $row['no_of_units'] : null,
    //             'total_ctn'         => !empty($row['total_ctn']) ? (int) $row['total_ctn'] : null,
    //             'rate'              => !empty($row['rate']) ? (float) $row['rate'] : null,
    //             'unit'              => $row['unit'] ?? null,
    //             'status'            => $row['status'] ?? null,
    //             'changes'           => $row['changes'] ?? null,
    //             'values'            => $row['values'] ?? null,
    //             'package_size'      => $row['package_size'] ?? null,
    //             'product_size_link' => $row['product_size_link'] ?? null,
    //             'comparison_link'   => $row['comparison_link'] ?? null,
    //             'order_link'        => $row['order_link'] ?? null,
    //             'image_src'         => $row['image_src'] ?? null,
    //             'photos'            => $row['photos'] ?? null,
    //             'specification'     => $row['specification'] ?? null,
    //             'supplier_names'    => $row['supplier_names'] ?? [],
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Inventory pushed successfully',
    //         'count'   => count($rows),
    //     ]);
    // }


    public function pushInventory(Request $request)
    {
        $tabName = $request->input('tab_name');
        $rows = $request->input('data', []);

        foreach ($rows as $row) {
            $sku = trim($row['our_sku'] ?? '');
            // $incomingQty = !empty($row['no_of_units']) ? (int) $row['no_of_units'] : 0;
            $units = !empty($row['no_of_units']) ? (int) $row['no_of_units'] : 0;
            $ctns  = !empty($row['total_ctn']) ? (int) $row['total_ctn'] : 0;

            $qty = $units * $ctns;


            if (!$sku || $qty <= 0) {
                continue;
            }

            try {
                // --- STEP 1: Find inventory_item_id from products.json ---
                $inventoryItemId = null;
                $pageInfo = null;
                $normalizedSku = strtoupper(preg_replace('/\s+/u', ' ', $sku));

                do {
                    $queryParams = ['limit' => 250];
                    if ($pageInfo) $queryParams['page_info'] = $pageInfo;

                    $response = Http::withBasicAuth(config('services.shopify.api_key'), config('services.shopify.password'))
                        ->get("https://" . config('services.shopify.store_url') . "/admin/api/2025-01/products.json", $queryParams);

                    $products = $response->json('products') ?? [];

                    foreach ($products as $product) {
                        foreach ($product['variants'] as $variant) {
                            $variantSku = strtoupper(preg_replace('/\s+/u', ' ', trim($variant['sku'] ?? '')));
                            if ($variantSku === $normalizedSku) {
                                $inventoryItemId = $variant['inventory_item_id'];
                                break 2;
                            }
                        }
                    }

                    // Pagination
                    $linkHeader = $response->header('Link');
                    $pageInfo = null;
                    if ($linkHeader && preg_match('/<([^>]+page_info=([^&>]+)[^>]*)>; rel="next"/', $linkHeader, $matches)) {
                        $pageInfo = $matches[2];
                    }
                } while (!$inventoryItemId && $pageInfo);

                if (!$inventoryItemId) {
                    Log::warning("Shopify SKU not found: {$sku}");
                    continue; // skip if SKU not in Shopify
                }

                // --- STEP 2: Get location_id from inventory_levels ---
                $invLevelResponse = Http::withBasicAuth(config('services.shopify.api_key'), config('services.shopify.password'))
                    ->get("https://" . config('services.shopify.store_url') . "/admin/api/2025-01/inventory_levels.json", [
                        'inventory_item_ids' => $inventoryItemId,
                    ]);

                $levels = $invLevelResponse->json('inventory_levels') ?? [];
                $locationId = $levels[0]['location_id'] ?? null;

                if (!$locationId) {
                    Log::warning("Shopify location not found for SKU: {$sku}");
                    continue;
                }

                // --- STEP 3: Adjust Shopify available qty ---
                $adjustResponse = Http::withBasicAuth(config('services.shopify.api_key'), config('services.shopify.password'))
                    ->post("https://" . config('services.shopify.store_url') . "/admin/api/2025-01/inventory_levels/adjust.json", [
                        'inventory_item_id' => $inventoryItemId,
                        'location_id' => $locationId,
                        'available_adjustment' => $qty,
                    ]);

                if (!$adjustResponse->successful()) {
                    Log::error("Failed to adjust Shopify inventory for SKU: {$sku}", $adjustResponse->json());
                    continue;
                }

                // --- STEP 4: Store in local DB ---
                InventoryWarehouse::create([
                    'tab_name'          => $row['tab_name'] ?? $tabName,
                    'supplier_name'     => $row['supplier_name'] ?? null,
                    'company_name'      => $row['company_name'] ?? null,
                    'our_sku'           => $sku,
                    'parent'            => $row['parent'] ?? null,
                    'no_of_units'       => $units,
                    'total_ctn'         => $ctns,
                    'rate'              => !empty($row['rate']) ? (float) $row['rate'] : null,
                    'unit'              => $row['unit'] ?? null,
                    'status'            => $row['status'] ?? null,
                    'changes'           => $row['changes'] ?? null,
                    'values'            => $row['values'] ?? null,
                    'package_size'      => $row['package_size'] ?? null,
                    'product_size_link' => $row['product_size_link'] ?? null,
                    'comparison_link'   => $row['comparison_link'] ?? null,
                    'order_link'        => $row['order_link'] ?? null,
                    'image_src'         => $row['image_src'] ?? null,
                    'photos'            => $row['photos'] ?? null,
                    'specification'     => $row['specification'] ?? null,
                    'supplier_names'    => $row['supplier_names'] ?? [],
                ]);

            } catch (\Exception $e) {
                Log::error("PushInventory failed for SKU {$sku}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory pushed successfully to Shopify and stored',
            'count'   => count($rows),
        ]);
    }


}
