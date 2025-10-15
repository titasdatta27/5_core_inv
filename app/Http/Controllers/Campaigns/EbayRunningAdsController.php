<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\EbayDataView;
use App\Models\EbayMetric;
use App\Models\EbayPriorityReport;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use Illuminate\Http\Request;

class EbayRunningAdsController extends Controller
{
    public function index()
    {
        return view('campaign.ebay-running-ads');
    }

    public function getEbayRunningAdsData()
    {
        $normalizeSku = fn($sku) => strtoupper(trim($sku));

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));

        $ebayMetricData = EbayMetric::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));

        $nrValues = EbayDataView::whereIn('sku', $skus)->pluck('value', 'sku');

        $ebayCampaignReportsL30 = EbayPriorityReport::where('report_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })
            ->get();

        $ebayCampaignReportsL7 = EbayPriorityReport::where('report_range', 'L7')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })
            ->get();

        $result = [];

        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            $parent = $pm->parent;

            $shopify = $shopifyData[$sku] ?? null;

            $ebay = $ebayMetricData[$sku] ?? null;

            $matchedCampaignL30 = $ebayCampaignReportsL30->first(function ($item) use ($sku) {
                return strtoupper(trim($item->campaign_name)) === strtoupper(trim($sku));
            });

            $matchedCampaignL7 = $ebayCampaignReportsL7->first(function ($item) use ($sku) {
                return strtoupper(trim($item->campaign_name)) === strtoupper(trim($sku));
            });

            $row = [];
            $row['parent'] = $parent;
            $row['sku']    = $pm->sku;
            $row['INV']    = $shopify->inv ?? 0;
            $row['L30']    = $shopify->quantity ?? 0;
            $row['e_l30']  = $ebay->ebay_l30 ?? 0;
            $row['campaignName'] = $matchedCampaignL7->campaign_name ?? ($matchedCampaignL30->campaign_name ?? '');

            //kw
            $row['kw_spend_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_ad_fees_payout_currency ?? 0);
            $row['kw_spend_L7']  = (float) str_replace('USD ', '', $matchedCampaignL7->cpc_ad_fees_payout_currency ?? 0);
            $row['kw_sales_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_sale_amount_payout_currency ?? 0);
            $row['kw_sales_L7']  = (float) str_replace('USD ', '', $matchedCampaignL7->cpc_sale_amount_payout_currency ?? 0);
            $row['kw_sold_L30']  = (int) ($matchedCampaignL30->cpc_attributed_sales ?? 0);
            $row['kw_sold_L7']   = (int) ($matchedCampaignL7->cpc_attributed_sales ?? 0);
            $row['kw_clicks_L30'] = (int) ($matchedCampaignL30?->cpc_clicks ?? 0);
            $row['kw_clicks_L7']  = (int) ($matchedCampaignL7?->cpc_clicks ?? 0);
            $row['kw_impr_L30']     = (int) ($matchedCampaignL30?->cpc_impressions ?? 0);
            $row['kw_impr_L7']      = (int) ($matchedCampaignL7?->cpc_impressions ?? 0);

            $row['SPEND_L30'] = $row['kw_spend_L30'];
            $row['SPEND_L7']  = $row['kw_spend_L7'];
            $row['SALES_L30'] = $row['kw_sales_L30'];
            $row['SALES_L7']  = $row['kw_sales_L7'];
            $row['SOLD_L30']  = $row['kw_sold_L30'];
            $row['SOLD_L7']   = $row['kw_sold_L7'];
            $row['CLICKS_L30'] = $row['kw_clicks_L30'];
            $row['CLICKS_L7']  = $row['kw_clicks_L7'];
            $row['IMP_L30']   = $row['kw_impr_L30'];
            $row['IMP_L7']    = $row['kw_impr_L7'];

            $row['NR'] = '';
            if (isset($nrValues[$pm->sku])) {
                $raw = $nrValues[$pm->sku];
                if (!is_array($raw)) {
                    $raw = json_decode($raw, true);
                }
                if (is_array($raw)) {
                    $row['NR'] = $raw['NR'] ?? '';
                }
            }

            if($row['campaignName'] !== ''){
                $result[] = $row;
            }
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data'    => $result,
            'status'  => 200,
        ]);

    }
}
