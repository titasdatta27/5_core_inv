<?php

namespace App\Http\Controllers;

use App\Models\EbayDataView;
use App\Models\EbayPriorityReport;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EbayMissingAdsController extends Controller
{
    public function index()
    {
        return view('campaign.ebay-missing-ads');
    }

    public function getEbayMissingAdsData()
    {
        try {
            $normalizeSku = fn($sku) => strtoupper(trim($sku));

            $productMasters = ProductMaster::orderBy('parent', 'asc')
                ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
                ->orderBy('sku', 'asc')
                ->get();

            if ($productMasters->isEmpty()) {
                return response()->json([
                    'message' => 'No product masters found',
                    'data'    => [],
                    'status'  => 200,
                ]);
            }

            $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();

            // Fetch all required data
            $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));
            $nrValues = EbayDataView::whereIn('sku', $skus)->pluck('value', 'sku');
            $ebayMetricData = DB::connection('apicentral')->table('ebay_one_metrics')
                ->select('sku', 'ebay_price', 'item_id')
                ->whereIn('sku', $skus)
                ->get()
                ->keyBy(fn($item) => $normalizeSku($item->sku));

            // Fetch campaign reports and create efficient lookup
            $ebayCampaignReports = EbayPriorityReport::where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })->get();

            $campaignLookup = [];
            foreach ($ebayCampaignReports as $campaign) {
                foreach ($skus as $sku) {
                    if (strpos($campaign->campaign_name, $sku) !== false) {
                        if (!isset($campaignLookup[$sku])) {
                            $campaignLookup[$sku] = $campaign;
                        }
                    }
                }
            }

            $campaignListings = DB::connection('apicentral')
                ->table('ebay_campaign_ads_listings')
                ->select('listing_id', 'bid_percentage')
                ->get()
                ->keyBy('listing_id')
                ->toArray();

            $result = [];

            foreach ($productMasters as $pm) {
                $sku = strtoupper($pm->sku);
                $shopify = $shopifyData->get($sku);
                $ebayMetric = $ebayMetricData->get($sku);
                $campaignReport = $campaignLookup[$sku] ?? null;
                
                $nrValue = $nrValues->get($sku);
                $nrActual = is_array($nrValue) ? ($nrValue['NR'] ?? null) : null;

                $result[] = [
                    'sku' => $sku,
                    'parent' => $pm->parent,
                    'INV' => $shopify->inv ?? 0,
                    'L30' => $shopify->quantity ?? 0,
                    'NRA' => $nrActual,
                    'kw_campaign_name' => $campaignReport->campaign_name ?? null,
                    'pmt_bid_percentage' => ($ebayMetric && isset($ebayMetric->item_id) && isset($campaignListings[$ebayMetric->item_id])) 
                        ? $campaignListings[$ebayMetric->item_id]->bid_percentage 
                        : null,
                    'campaignStatus' => $campaignReport->campaignStatus ?? null,
                ];
            }

            return response()->json([
                'message' => 'Data fetched successfully',
                'data'    => $result,
                'status'  => 200,
            ]);
            
        } catch (\Exception $e) {
            Log::error('EbayMissingAdsController error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error fetching data: ' . $e->getMessage(),
                'data'    => [],
                'status'  => 500,
            ]);
        }
    }
}
