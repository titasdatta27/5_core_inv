<?php

namespace App\Http\Controllers\PurchaseMaster;

use App\Http\Controllers\Controller;
use App\Models\MfrgProgress;
use App\Models\ReadyToShip;
use App\Models\Supplier;
use App\Models\TransitContainerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReadyToShipController extends Controller
{
    public function index()
    {
        $supplierRows = Supplier::where('type', 'Supplier')->get();

        $supplierMapByParent = [];
        foreach ($supplierRows as $row) {
            $parents = array_map('trim', explode(',', strtoupper($row->parent ?? '')));
            foreach ($parents as $parent) {
                if (!empty($parent)) {
                    $supplierMapByParent[$parent][] = $row->name;
                }
            }
        }

        foreach ($supplierMapByParent as $parent => $suppliers) {
            $supplierMapByParent[$parent] = array_unique($suppliers);
        }

        $shopifyImages = DB::table('shopify_skus')
            ->select('sku', 'image_src')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim($item->sku)));

        $productMaster = DB::table('product_master')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim($item->sku)));

        $readyToShipData = ReadyToShip::where('transit_inv_status', 0)->get();

        $readyToShipData->transform(function ($item) use ($supplierMapByParent, $shopifyImages, $productMaster) {
            $sku = strtoupper(trim($item->sku));
            $parent = strtoupper(trim($item->parent ?? ''));
            $item->supplier_names = $supplierMapByParent[$parent] ?? [];

            $image = null;
            $cbm = null;

            if (isset($shopifyImages[$sku]) && !empty($shopifyImages[$sku]->image_src)) {
                $image = $shopifyImages[$sku]->image_src;
            }

            if (!isset($productMaster[$sku])) {
                Log::warning("SKU missing in product_master: [$sku] <- original: [{$item->sku}]");
            } else {
                $valuesRaw = $productMaster[$sku]->Values ?? '{}';
                $values = json_decode($valuesRaw, true);

                if (is_array($values)) {
                    if (!empty($values['image_path'])) {
                        $image = 'storage/' . ltrim($values['image_path'], '/');
                    }

                    if (isset($values['cbm'])) {
                        $cbm = (float) $values['cbm'];
                    } else {
                        Log::warning("CBM missing in values for SKU: $sku");
                    }
                } else {
                    Log::warning("Values decode failed for SKU: $sku");
                }
            }

            $item->Image = $image;
            $item->CBM = $cbm;
            return $item;
        });

        return view('purchase-master.ready-to-ship.index', [
            'readyToShipList' => $readyToShipData,
            'suppliers' => Supplier::pluck('name'),
        ]);
    }


    public function inlineUpdateBySku(Request $request)
    {
        $sku = $request->input('sku');
        $column = $request->input('column');
        $value = $request->input('value');

        if (!in_array($column, [
            'qty',
            'rate',
            'area',
            'pay_term',
            'payment_confirmation',
        ])) {
            return response()->json(['success' => false, 'message' => 'Invalid column.']);
        }

        $readyToShip = ReadyToShip::where('sku', $sku)->first();

        if (!$readyToShip) {
            return response()->json(['success' => false, 'message' => 'SKU not found in ready_to_ships']);
        }

        $readyToShip->$column = $value;
        $readyToShip->save();

        return response()->json(['success' => true]);
    }

    public function revertBackMfrg(Request $request)
    {
        $skus = $request->input('skus');

        if (!is_array($skus) || empty($skus)) {
            return response()->json(['success' => false, 'message' => 'No SKUs provided.']);
        }

        try {
            ReadyToShip::whereIn('sku', $skus)->delete();
            MfrgProgress::whereIn('sku', $skus)->update(['ready_to_ship' => 'No']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Action failed: ' . $e->getMessage()]);
        }
    }

    public function moveToTransit(Request $request)
    {
        $skus = $request->input('skus', []);
        $tabName = trim($request->input('tab_name'));

        if (empty($skus)) {
            return response()->json(['success' => false, 'message' => 'No SKUs provided.']);
        }

        $readyItems = ReadyToShip::whereIn('sku', $skus)->get();

        foreach ($readyItems as $item) {
            $existing = TransitContainerDetail::where('our_sku', $item->sku)->first();

            if ($existing) {
                $existing->update([
                    'our_sku'       => $item->sku,
                    'tab_name'      => $tabName,
                    'updated_at'    => now(),
                ]);
            } else {
                TransitContainerDetail::create([
                    'our_sku'       => $item->sku,
                    'tab_name'      => $tabName,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $item->update([
                'transit_inv_status' => 1,
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Data moved to TransitContainerDetail.']);
    }


}
