<?php

namespace App\Http\Controllers\ProductMaster;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\LinkedProductData;
use App\Models\Permission;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProductMasterController extends Controller
{
    protected $apiController;

    public function __construct(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }

    /**
     * Handle dynamic route parameters and return a view.
     */

    public function product_master_index(Request $request)
    {
        // Fetch all products sorted by ID first
        $allProducts = DB::table('product_master')->orderBy('id', 'asc')->get();

        // Group products by parent while maintaining original order for non-parent items
        $sortedProducts = [];
        $processedParents = [];

        foreach ($allProducts as $product) {
            // If this product has a parent and we haven't processed this parent group yet
            if ($product->parent && !in_array($product->parent, $processedParents)) {
                // Get all products with this parent
                $grouped = $allProducts->where('parent', $product->parent);
                foreach ($grouped as $item) {
                    $sortedProducts[] = (array) $item; // Convert to array
                }
                $processedParents[] = $product->parent;
            }
            // If this product doesn't have a parent and hasn't been added yet
            elseif (!$product->parent && !in_array($product->id, array_column($sortedProducts, 'id'))) {
                $sortedProducts[] = (array) $product; // Convert to array
            }
        }

        // Calculate statistics
        $totalSKUs = count($sortedProducts);
        $parentCount = collect($sortedProducts)->filter(function ($product) {
            return strpos($product['sku'], 'PARENT') !== false;
        })->count();
        // Calculate total LP and CP
        $totalLP = collect($sortedProducts)->sum('lp');
        $totalCP = collect($sortedProducts)->sum('cp');

        $emails = User::pluck('email')->toArray();

        // Fetch all role-based permissions
        $rolePermissions = Permission::all()->keyBy('role');

        // Build a map: email => columns based on user role
        $emailColumnMap = [];
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            $columns = [];
            if ($user && isset($rolePermissions[$user->role])) {
                $columns = $rolePermissions[$user->role]->permissions['product_lists'] ?? [];
            }
            $emailColumnMap[$email] = $columns;
        }

        // Get current user permissions based on role
        $userPermissions = [];
        if (auth()->check()) {
            $userRole = auth()->user()->role;
            $rolePermission = Permission::where('role', $userRole)->first();
            $userPermissions = $rolePermission ? $rolePermission->permissions : [];
        }

        return view('productmaster', [
            'products' => $sortedProducts,
            'totalSKUs' => $totalSKUs,
            'parentCount' => $parentCount,
            'totalLP' => number_format($totalLP, 2),
            'totalCP' => number_format($totalCP, 2),
            'emails' => $emails,
            'emailColumnMap' => $emailColumnMap, // Pass this to Blade
            'permissions' => $userPermissions, // Pass user permissions to view
        ]);
    }


    public function getViewProductData(Request $request)
    {
        // Fetch all products from the database ordered by parent and SKU
        $products = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END") 
            ->orderBy('sku', 'asc')
            ->get();

        // Fetch all shopify SKUs and key by SKU for fast lookup
        $shopifySkus = ShopifySku::all()->keyBy('sku');

        // Prepare data in the same format as your sheet (flatten Values)
        $result = [];
        foreach ($products as $product) {
            $row = [
                'id' => $product->id,
                'Parent' => $product->parent,
                'SKU' => $product->sku,
            ];

            // Merge the Values array (if not null)
            if (is_array($product->Values)) {
                $row = array_merge($row, $product->Values);
            } elseif (is_string($product->Values)) {
                $values = json_decode($product->Values, true);
                if (is_array($values)) {
                    $row = array_merge($row, $values);
                }
            }

            // Add Shopify inv and quantity if available
            $row['shopify_inv'] = $shopifySkus[$product->sku]->inv ?? null;
            $row['shopify_quantity'] = $shopifySkus[$product->sku]->quantity ?? null;

            $shopifyImage = $shopifySkus[$product->sku]->image_src ?? null;
            // image_path is inside $row (from Values JSON)
            $localImage = isset($row['image_path']) && $row['image_path'] ? $row['image_path'] : null;
            if ($shopifyImage) {
                $row['image_path'] = $shopifyImage; // Use Shopify URL
            } elseif ($localImage) {
                $row['image_path'] = '/' . ltrim($localImage, '/'); // Use local path, ensure leading slash
            } else {
                $row['image_path'] = null;
            }

            $result[] = $row;
        }

        return response()->json([
            'message' => 'Data loaded from database',
            'data' => $result,
            'status' => 200
        ]);
    }


    public function getProductBySku(Request $request)
{
    // Get SKU from query param (with spaces)
    $sku = $request->query('sku');

    if (!$sku) {
        return response()->json([
            'message' => 'SKU is required',
            'status' => 400
        ], 400);
    }

    // Normalize spaces (remove extra, keep inside)
    $sku = preg_replace('/\s+/', ' ', trim($sku));

    // Fetch product
    $product = ProductMaster::where('sku', $sku)->first();

    if (!$product) {
        return response()->json([
            'message' => "Product not found for SKU: {$sku}",
            'status' => 404
        ], 404);
    }

    // Shopify data
    $shopifySku = ShopifySku::where('sku', $sku)->first();

    // Build response
    $row = [
        'id'     => $product->id,
        'Parent' => $product->parent,
        'SKU'    => $product->sku,
    ];

    // Merge values JSON
    $values = $product->Values;
    if (is_array($values)) {
        $row = array_merge($row, $values);
    } elseif (is_string($values)) {
        $decoded = json_decode($values, true);
        if (is_array($decoded)) {
            $row = array_merge($row, $decoded);
        }
    }

    // Shopify fields
    $row['shopify_inv'] = $shopifySku->inv ?? null;
    $row['shopify_quantity'] = $shopifySku->quantity ?? null;

    // Image
    $shopifyImage = $shopifySku->image_src ?? null;
    $localImage = $row['image_path'] ?? null;
    if ($shopifyImage) {
        $row['image_path'] = $shopifyImage;
    } elseif ($localImage) {
        $row['image_path'] = '/' . ltrim($localImage, '/');
    } else {
        $row['image_path'] = null;
    }

    return response()->json([
        'message' => 'Product loaded successfully',
        'data' => $row,
        'status' => 200
    ]);
}



    
    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent' => 'nullable|string',
            'sku' => 'required|string',
            'Values' => 'required',
            'unit' => 'required|string',
            'image' => 'nullable|file|image|max:5120', // 5MB max
        ]);

        $operation = $request->input('operation', 'create');
        $originalSku = $request->input('original_sku');
        $originalParent = $request->input('original_parent');

        try {
            // Prepare Values as array
            $values = is_array($validated['Values']) ? $validated['Values'] : json_decode($validated['Values'], true);

            // Handle image upload if present
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('product_images', 'public');
                $values['image_path'] = 'storage/' . $imagePath;
            }

            if ($operation === 'update' && $originalSku) {
                // Find the product by original SKU and parent
                $query = ProductMaster::where('sku', $originalSku);
                if ($originalParent !== null) {
                    $query->where('parent', $originalParent);
                }
                $product = $query->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found for update.'
                    ], 404);
                }

                // Prepare for possible image deletion
                $oldValues = is_array($product->Values) ? $product->Values : json_decode($product->Values, true);
                $oldImagePath = !empty($oldValues['image_path']) ? public_path($oldValues['image_path']) : null;
                $newImageUploaded = false;

                // Handle image upload if present
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imagePath = $image->store('product_images', 'public');
                    $values['image_path'] = 'storage/' . $imagePath;
                    $newImageUploaded = true;
                } else {
                    // Keep old image_path if not uploading a new image
                    if (!empty($oldValues['image_path'])) {
                        $values['image_path'] = $oldValues['image_path'];
                    }
                }

                $product->sku = $validated['sku'];
                $product->parent = $validated['parent'];
                $product->Values = $values;
                $product->save();

                // Only delete old image after successful update and if a new image was uploaded
                if ($newImageUploaded && $oldImagePath && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product
                ]);
            } else {
                // Check for soft-deleted product with same SKU and Parent
                $existing = ProductMaster::withTrashed()
                    ->where('sku', $validated['sku'])
                    ->where('parent', $validated['parent'])
                    ->first();

                if ($existing && $existing->trashed()) {
                    // Restore and update
                    $existing->restore();
                    $existing->Values = $values;
                    $existing->save();
                    $product = $existing;
                } else {
                    // Create new product
                    $product = ProductMaster::create([
                        'sku' => $validated['sku'],
                        'parent' => $validated['parent'],
                        'Values' => $values,
                    ]);
                }

                // 2. Also create a row with parent = original parent, sku = 'PARENT {parent}', Values = null
                if (!empty($validated['parent'])) {
                    $parentSku = 'PARENT ' . $validated['parent'];
                    // Check for both existing and soft-deleted rows
                    $parentRow = ProductMaster::withTrashed()
                        ->where('sku', $parentSku)
                        ->where('parent', $validated['parent'])
                        ->first();

                    if ($parentRow) {
                        if ($parentRow->trashed()) {
                            $parentRow->restore();
                        }
                    } elseif (!$parentRow) {
                        // Only create if it doesn't exist at all
                        ProductMaster::create([
                            'sku' => $parentSku,
                            'parent' => $validated['parent'],
                            'Values' => null,
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product saved successfully',
                    'data' => $product
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // <-- TEMP: show real error
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            // Get product IDs (single or multiple)
            $productIds = $request->input('ids');

            if (empty($productIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products selected for deletion.',
                ], 400);
            }

            // Convert to array if single ID is provided
            if (!is_array($productIds)) {
                $productIds = [$productIds];
            }

            // Fetch products to delete
            $products = ProductMaster::whereIn('id', $productIds)->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching products found.',
                ], 404);
            }

            foreach ($products as $product) {
                // Delete image if exists
                if ($product->image && File::exists(public_path($product->image))) {
                    File::delete(public_path($product->image));
                }

                // Hard delete the product
                $product->delete();
            }

            return response()->json([
                'success' => true,
                'message' => count($productIds) . ' product(s) deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting product(s): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product(s). Please try again.'
            ], 500);
        }
    }

    public function importFromSheet(Request $request)
    {
        $sheetData = $request->input('data');
        $imported = 0;
        $errors = [];

        // Define your mapping here (sheet column => db key)
        $keyMap = [
            'LP' => 'lp',
            'CP' => 'cp',
            'FRGHT' => 'frght',
            'SHIP' => 'ship',
            'SHIP FBA' => 'ship_fba',
            'SHIP Temu' => 'ship_temu',
            'SHIP eBay2' => 'ship_ebay2',
            'Label QTY' => 'label_qty',
            'WT ACT' => 'wt_act',
            'WT DECL' => 'wt_decl',
            'L' => 'l',
            'W' => 'w',
            'H' => 'h',
            'CBM' => 'cbm',
            'DC' => 'dc',
            '5C' => 'l2_url',
            'Pcs/Box' => 'pcs_per_box',
            'L1' => 'l1',
            'B' => 'b',
            'H1' => 'h1',
            'Weight' => 'weight',
            'MSRP' => 'msrp',
            'MAP' => 'map',
            'STATUS' => 'status',
            'UPC' => 'upc',
            'Initial Quantity' => 'initial_quantity'
        ];

        foreach ($sheetData as $row) {
            try {
                $parent = $row['Parent'] ?? null;
                $sku = $row['SKU'] ?? null;

                if (!$sku) {
                    $errors[] = 'SKU missing in row: ' . json_encode($row);
                    continue;
                }

                // Build the transformed array with all keys, set to null if missing
                $transformed = [];
                foreach ($keyMap as $sheetKey => $dbKey) {
                    $transformed[$dbKey] = array_key_exists($sheetKey, $row) && $row[$sheetKey] !== '' ? $row[$sheetKey] : null;
                }

                ProductMaster::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'parent' => $parent,
                        'Values' => $transformed,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Error for SKU ' . ($row['SKU'] ?? 'N/A') . ': ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    public function batchUpdate(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:product_master,id',
            'operations' => 'required|array',
            'operations.*.field' => 'required|string',
            'operations.*.operation' => 'required|string|in:set,add,subtract,multiply,divide',
            'operations.*.value' => 'required',
        ]);

        DB::beginTransaction();

        try {
            foreach ($data['items'] as $item) {
                $product = ProductMaster::find($item['id']);

                if (!$product) {
                    continue;
                }

                // Decode the existing Values JSON into an associative array
                $values = is_array($product->Values) ? $product->Values : json_decode($product->Values, true);

                foreach ($data['operations'] as $operation) {
                    $field = $operation['field'];
                    $value = $operation['value'];

                    if (!isset($values[$field])) {
                        $values[$field] = 0; // Initialize if not present, this assumes numerical value for arithmetic operations
                    }

                    switch ($operation['operation']) {
                        case 'set':
                            $values[$field] = $value;
                            break;
                        case 'add':
                            $values[$field] += $value;
                            break;
                        case 'subtract':
                            $values[$field] -= $value;
                            break;
                        case 'multiply':
                            $values[$field] *= $value;
                            break;
                        case 'divide':
                            if ($value != 0) {
                                $values[$field] /= $value;
                            }
                            break;
                    }
                }

                // Encode the updated values back to JSON and save
                $product->Values = $values;
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Products updated successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Batch update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update products. Please try again.'
            ], 500);
        }
    }

    public function linkedProductsView(){

        $warehouses = Warehouse::select('id', 'name')->get();

        $skus = ProductMaster::select('product_master.id', 'product_master.parent', 'product_master.sku', 'shopify_skus.inv as available_quantity', 'shopify_skus.quantity as l30')
            ->leftJoin('shopify_skus', 'product_master.sku', '=', 'shopify_skus.sku')
            ->get()
            ->map(function ($item) {
            $inv = $item->available_quantity ?? 0;
            $l30 = $item->l30 ?? 0;
            $item->dil = $inv != 0 ? round(($l30 / $inv) * 100) : 0;
            return $item;
        });

        return view('inventory-management.linked-products-view', compact('warehouses', 'skus'));
    }

    public function linkedProductStore(Request $request)
    {
        $request->validate([
            'group_id' => 'required|numeric',
            'from_sku' => 'required|string|different:to_sku',
            'to_sku' => 'required|string|different:from_sku',
        ]);

        // Update both SKUs with given group_id
        ProductMaster::whereIn('sku', [$request->from_sku, $request->to_sku])
            ->update(['group_id' => $request->group_id]);

        return response()->json([
            'success' => true,
            'message' => 'Products linked successfully',
            'group_id' => $request->group_id,
        ]);
    }

    public function linkedProductsList()
    {
         $data = ProductMaster::whereNotNull('group_id')
        ->orderBy('group_id')
        ->get()
        ->groupBy('group_id') // group all SKUs under the same group_id
        ->map(function ($group, $groupId) {
            return [
                'group_id' => $groupId,
                'skus'     => $group->pluck('sku')->toArray(),
                'parents'  => $group->pluck('parent')->unique()->toArray(),
            ];
        })
        ->values(); // reset keys for JSON

        return response()->json(['data' => $data]);
    }

    public function showUpdatedQty()
    {
        return view('inventory-management.auto-updated-qty');
    }

    // Data for DataTable / AJAX
    public function showUpdatedQtyList(Request $request)
    {
        $data = LinkedProductData::latest()->get(['group_id', 'sku', 'old_qty', 'new_qty']);

        return response()->json($data);
    }
}
