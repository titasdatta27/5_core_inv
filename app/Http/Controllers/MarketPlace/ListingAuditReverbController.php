<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\ProductMaster;
use App\Models\ReverbViewData;
use App\Models\ShopifySku;
use Illuminate\Http\Request;

class ListingAuditReverbController extends Controller
{
    public function listingAuditReverb(Request $request)
    {
        $mode = $request->query('mode');
        $demo = $request->query('demo');

        return view('market-places.listingAuditReverb', [
            'mode' => $mode,
            'demo' => $demo
        ]);
    }

    public function getViewListingAuditReverbData()
    {
        $productMasters = ProductMaster::all();
        $skus = $productMasters->pluck('sku')->unique()->toArray();
        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy('sku');
        $dataViewValues = ReverbViewData::whereIn('sku', $skus)->pluck('values', 'sku');


        $processedData = $productMasters->map(function ($item) use ($shopifyData, $dataViewValues) {
            $childSku = strtoupper(trim($item->sku));
            $parent = $item->parent ?? '';
            $isParent = stripos($childSku, 'PARENT') !== false;

            $item->INV = $shopifyData[$childSku]->inv ?? 0;
            $item->L30 = $shopifyData[$childSku]->quantity ?? 0;
            $item->Parent = $parent;
            $item->is_parent = $isParent;

            // Attach dataView fields if available
            $item->Listed = false;
            $item->Live = false;
            $item->Category = false;
            $item->AttrFilled = false;
            $item->APlus = false;
            $item->Video = false;
            $item->Title = false;
            $item->Images = false;
            $item->Description = false;
            $item->BulletPoints = false;
            $item->InVariation = false;

            if ($childSku && isset($dataViewValues[$childSku])) {
                $raw = $dataViewValues[$childSku];
                $data = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);
                $item->Listed = filter_var($data['Listed'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Live = filter_var($data['Live'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Category = filter_var($data['Category'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->AttrFilled = filter_var($data['AttrFilled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->APlus = filter_var($data['APlus'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Video = filter_var($data['Video'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Title = filter_var($data['Title'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Images = filter_var($data['Images'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->Description = filter_var($data['Description'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->BulletPoints = filter_var($data['BulletPoints'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item->InVariation = filter_var($data['InVariation'] ?? false, FILTER_VALIDATE_BOOLEAN);
            }

            return $item;
        })->values();

        return response()->json([
            'data' => $processedData,
            'status' => 200,
        ]);
    }

    public function saveAuditToDatabase(Request $request)
    {
        $sku = $request->input('sku');
        $field = $request->input('field');
        $value = $request->input('value'); // Changed from 'values' to 'value'

        if (!$sku || !$field) {
            return response()->json(['error' => 'SKU and field are required.'], 400);
        }

        $product = ProductMaster::where('sku', $sku)->first();
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $dataView = ReverbViewData::firstOrNew(['sku' => $sku]);
        $data = is_array($dataView->values) ? $dataView->values : (json_decode($dataView->values, true) ?: []);
        $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $dataView->values = $data;
        $dataView->save();

        return response()->json(['success' => true, 'data' => $dataView]);
    }
}