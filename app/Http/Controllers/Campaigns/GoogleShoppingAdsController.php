<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleShoppingAdsController extends Controller
{
    public function index(){
        return view('campaign.google-shopping-ads');
    }

    public function googleOverUtilizeView(){
        return view('campaign.google-shopping-over-utilize');
    }

    public function googleUnderUtilizeView(){
        return view('campaign.google-shopping-under-utilize');
    }

    public function googleShoppingSerp(){
        return view('campaign.google-shopping-ads-serp');
    }

    public function googleShoppingPmax(){
        return view('campaign.google-shopping-ads-pmax');
    }

    public function googleShoppingAdsRunning(){
        return view('campaign.google-shopping-ads-running');
    }

    public function getGoogleSearchShoppingAdsData(){

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy('sku');

        $googleCampaigns = DB::connection('apicentral')
            ->table('google_ads_campaigns')
            ->select(
                'campaign_name',
                'campaign_status',
                'budget_amount_micros',
                'range_type',
                'metrics_cost_micros',
                'metrics_clicks',
                'metrics_impressions'
            )
            ->whereIn('range_type', ['L1', 'L7', 'L30']) 
            ->get();

        $ranges = ['L1', 'L7', 'L30'];

        $result = [];

        foreach ($productMasters as $pm) {
            $sku = strtoupper(trim($pm->sku));
            $parent = $pm->parent;

            $shopify = $shopifyData[$sku] ?? null;

            $matchedCampaign = $googleCampaigns->first(function ($c) use ($sku) {
                $campaign = strtoupper(trim($c->campaign_name));
                $skuTrimmed = strtoupper(trim($sku));
                
                if (!str_ends_with($campaign, 'SEARCH.')) {
                    return false;
                }
                
                $contains = strpos($campaign, $skuTrimmed) !== false;
                
                $parts = array_map('trim', explode(',', $campaign));
                $exactMatch = in_array($skuTrimmed, $parts);
                
                return ($contains || $exactMatch) && $c->campaign_status === 'ENABLED';
            });

            $row = [];
            $row['parent'] = $parent;
            $row['sku']    = $pm->sku;
            $row['INV']    = $shopify->inv ?? 0;
            $row['L30']    = $shopify->quantity ?? 0;
            
            $row['campaignName'] = $matchedCampaign->campaign_name ?? null;
            $row['campaignBudgetAmount'] = $matchedCampaign->budget_amount_micros ?? null;
            $row['campaignBudgetAmount'] = $row['campaignBudgetAmount'] ? $row['campaignBudgetAmount'] / 1000000 : null;
            $row['status'] = $matchedCampaign->campaign_status ?? null;

            foreach ($ranges as $range) {
                $campaignRange = $googleCampaigns->first(function ($c) use ($sku, $range) {
                    $campaign = strtoupper(trim($c->campaign_name));
                    $skuTrimmed = strtoupper(trim($sku));
                    
                    $contains = strpos($campaign, $skuTrimmed) !== false;
                    
                    $parts = array_map('trim', explode(',', $campaign));
                    $exactMatch = in_array($skuTrimmed, $parts);
                    
                    return ($contains || $exactMatch) && $c->range_type === $range && $c->campaign_status === 'ENABLED';
                });


                $row["spend_$range"] = isset($campaignRange->metrics_cost_micros)
                    ? $campaignRange->metrics_cost_micros / 1000000
                    : 0;

                $row["clicks_$range"] = $campaignRange->metrics_clicks ?? 0;
                $row["impressions_$range"] = $campaignRange->metrics_impressions ?? 0;
                $row["cpc_$range"] = $row["clicks_$range"] ? $row["spend_$range"] / $row["clicks_$range"] : 0;
            }

            Log::info('Row Data:', ['budget' => $row['campaignBudgetAmount'], 'spend' => $row['spend_L30'], 'clicks' => $row['clicks_L30'], 'cpc' => $row['cpc_L30'], 'impressions' => $row['impressions_L30'], 'campaign' => $row['campaignName']]);

            if($row['campaignName'] != '') {
                $result[] = (object) $row;
            }

        }
        
        $uniqueResult = collect($result)->unique('sku')->values()->all();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data'    => $uniqueResult,
            'status'  => 200,
        ]);
    }

    public function getGoogleShoppingAdsData(){

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy('sku');

        $googleCampaigns = DB::connection('apicentral')
            ->table('google_ads_campaigns')
            ->select(
                'campaign_name',
                'campaign_status',
                'budget_amount_micros',
                'range_type',
                'metrics_cost_micros',
                'metrics_clicks',
                'metrics_impressions'
            )
            ->whereIn('range_type', ['L1', 'L7', 'L30']) 
            ->get();

        $ranges = ['L1', 'L7', 'L30'];

        $result = [];

        foreach ($productMasters as $pm) {
            $sku = strtoupper(trim($pm->sku));
            $parent = $pm->parent;

            $shopify = $shopifyData[$sku] ?? null;

            $matchedCampaign = $googleCampaigns->first(function ($c) use ($sku) {
                $campaign = strtoupper(trim($c->campaign_name));
                $skuTrimmed = strtoupper(trim($sku));
                
                if (!str_ends_with($campaign, 'SEARCH.')) {
                    return false;
                }
                
                $contains = strpos($campaign, $skuTrimmed) !== false;
                
                $parts = array_map('trim', explode(',', $campaign));
                $exactMatch = in_array($skuTrimmed, $parts);
                
                return ($contains || $exactMatch) && $c->campaign_status === 'ENABLED';
            });

            $row = [];
            $row['parent'] = $parent;
            $row['sku']    = $pm->sku;
            $row['INV']    = $shopify->inv ?? 0;
            $row['L30']    = $shopify->quantity ?? 0;
            
            $row['campaignName'] = $matchedCampaign->campaign_name ?? null;
            $row['campaignBudgetAmount'] = $matchedCampaign->budget_amount_micros ?? null;
            $row['campaignBudgetAmount'] = $row['campaignBudgetAmount'] ? $row['campaignBudgetAmount'] / 1000000 : null;
            $row['status'] = $matchedCampaign->campaign_status ?? null;

            foreach ($ranges as $range) {
                $campaignRange = $googleCampaigns->first(function ($c) use ($sku, $range) {
                    $campaign = strtoupper(trim($c->campaign_name));
                    $skuTrimmed = strtoupper(trim($sku));
                    
                    $contains = strpos($campaign, $skuTrimmed) !== false;
                    
                    $parts = array_map('trim', explode(',', $campaign));
                    $exactMatch = in_array($skuTrimmed, $parts);
                    
                    return ($contains || $exactMatch) && $c->range_type === $range && $c->campaign_status === 'ENABLED';
                });


                $row["spend_$range"] = isset($campaignRange->metrics_cost_micros)
                    ? $campaignRange->metrics_cost_micros / 1000000
                    : 0;

                $row["clicks_$range"] = $campaignRange->metrics_clicks ?? 0;
                $row["impressions_$range"] = $campaignRange->metrics_impressions ?? 0;
                $row["cpc_$range"] = $row["clicks_$range"] ? $row["spend_$range"] / $row["clicks_$range"] : 0;
            }

            Log::info('Row Data:', ['budget' => $row['campaignBudgetAmount'], 'spend' => $row['spend_L30'], 'clicks' => $row['clicks_L30'], 'cpc' => $row['cpc_L30'], 'impressions' => $row['impressions_L30'], 'campaign' => $row['campaignName']]);

            if($row['campaignName'] != '') {
                $result[] = (object) $row;
            }

        }
        
        $uniqueResult = collect($result)->unique('sku')->values()->all();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data'    => $uniqueResult,
            'status'  => 200,
        ]);
    }
}
