<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use App\Models\EbayDataView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListingAuditEbayController extends Controller
{
    public function listingAuditEbay(Request $request)
    {
        $mode = $request->query('mode');
        $demo = $request->query('demo');
        $percentage = Cache::remember('ebay_marketplace_percentage', now()->addDays(30), function () {
            return 100;
        });

        return view('market-places.listingAuditEbay', [
            'mode' => $mode,
            'demo' => $demo,
            'ebayPercentage' => $percentage
        ]);
    }

    public function getViewListingAuditEbayData(Request $request)
    {
        $productMasters = ProductMaster::all();
        $skus = $productMasters->pluck('sku')->unique()->toArray();
        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy('sku');
        $ebayDataViewValues = EbayDataView::whereIn('sku', $skus)->pluck('value', 'sku');


        $processedData = $productMasters->map(function ($item) use ($shopifyData, $ebayDataViewValues) {
            $childSku = strtoupper(trim($item->sku));
            $parent = $item->parent ?? '';
            $isParent = stripos($childSku, 'PARENT') !== false;

            $item->INV = $shopifyData[$childSku]->inv ?? 0;
            $item->L30 = $shopifyData[$childSku]->quantity ?? 0;
            $item->Parent = $parent;
            $item->is_parent = $isParent;

            // Attach eBayDataView fields if available
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
            $item->NR = 'REQ';

            if ($childSku && isset($ebayDataViewValues[$childSku])) {
                $raw = $ebayDataViewValues[$childSku];
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
                $item->NR = $data['NR'] ?? 'REQ';
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
        $value = $request->input('value');

        if (!$sku || !$field) {
            return response()->json(['error' => 'SKU and field are required.'], 400);
        }

        $product = ProductMaster::where('sku', $sku)->first();
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $ebayDataView = EbayDataView::firstOrNew(['sku' => $sku]);
        $data = is_array($ebayDataView->value) ? $ebayDataView->value : (json_decode($ebayDataView->value, true) ?: []);
        $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $ebayDataView->value = $data;
        $ebayDataView->save();

        return response()->json(['success' => true, 'data' => $ebayDataView]);
    }
}