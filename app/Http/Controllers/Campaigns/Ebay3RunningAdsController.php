<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Ebay3GeneralReport;
use App\Models\Ebay3PriorityReport;
use App\Models\EbayThreeDataView;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Ebay3RunningAdsController extends Controller
{
    public function index()
    {
        return view('campaign.ebay-three.ebay-running-ads');
    }

    public function getEbay3RunningAdsData()
    {
        $normalizeSku = fn($sku) => strtoupper(trim($sku));

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));

        $ebayMetricData = DB::table('ebay_3_metrics')
            ->select('sku', 'ebay_price', 'item_id')
            ->whereIn('sku', $skus)
            ->get()
            ->keyBy(fn($item) => $normalizeSku($item->sku));

        $nrValues = EbayThreeDataView::whereIn('sku', $skus)->pluck('value', 'sku');

        $ebayCampaignReportsL30 = Ebay3PriorityReport::where('report_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })
            ->get();

        $ebayCampaignReportsL7 = Ebay3PriorityReport::where('report_range', 'L7')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })
            ->get();

        $itemIds = $ebayMetricData->pluck('item_id')->toArray();
        
        $ebayGeneralReportsL30 = Ebay3GeneralReport::where('report_range', 'L30')
            ->whereIn('listing_id', $itemIds)
            ->get();

        $ebayGeneralReportsL7 = Ebay3GeneralReport::where('report_range', 'L7')
            ->whereIn('listing_id', $itemIds)
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
            
            $matchedGeneralL30 = $ebayGeneralReportsL30->first(function ($item) use ($ebay) {
                if (!$ebay || empty($ebay->item_id)) return false;
                return trim((string)$item->listing_id) == trim((string)$ebay->item_id);
            });

            $matchedGeneralL7 = $ebayGeneralReportsL7->first(function ($item) use ($ebay) {
                if (!$ebay || empty($ebay->item_id)) return false;
                return trim((string)$item->listing_id) == trim((string)$ebay->item_id);
            });

            $row = [];

            $row['parent'] = $parent;
            $row['sku'] = $pm->sku;
            $row['INV'] = $shopify->inv ?? 0;
            $row['L30'] = $shopify->quantity ?? 0;
            $row['e_l30'] = $ebay->ebay_l30 ?? 0;
            $row['campaignName'] = $matchedCampaignL7->campaign_name ?? ($matchedCampaignL30->campaign_name ?? '');

            //kw
            $row['kw_spend_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_ad_fees_payout_currency ?? 0);
            $row['kw_spend_L7'] = (float) str_replace('USD ', '', $matchedCampaignL7->cpc_ad_fees_payout_currency ?? 0);
            $row['kw_sales_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_sale_amount_payout_currency ?? 0);
            $row['kw_sales_L7'] = (float) str_replace('USD ', '', $matchedCampaignL7->cpc_sale_amount_payout_currency ?? 0);
            $row['kw_sold_L30'] = (int) ($matchedCampaignL30->cpc_attributed_sales ?? 0);
            $row['kw_sold_L7'] = (int) ($matchedCampaignL7->cpc_attributed_sales ?? 0);
            $row['kw_clicks_L30'] = (int) ($matchedCampaignL30?->cpc_clicks ?? 0);
            $row['kw_clicks_L7'] = (int) ($matchedCampaignL7?->cpc_clicks ?? 0);
            $row['kw_impr_L30'] = (int) ($matchedCampaignL30?->cpc_impressions ?? 0);
            $row['kw_impr_L7'] = (int) ($matchedCampaignL7?->cpc_impressions ?? 0);

            //pmt
            $row['pmt_spend_L30'] = (float) str_replace('USD ', '', $matchedGeneralL30->ad_fees ?? 0);
            $row['pmt_sales_L30'] = (float) str_replace('USD ', '', $matchedGeneralL30->sale_amount ?? 0);
            $row['pmt_spend_L7'] = (float) str_replace('USD ', '', $matchedGeneralL7->ad_fees ?? 0);
            $row['pmt_sales_L7'] = (float) str_replace('USD ', '', $matchedGeneralL7->sale_amount ?? 0);

            $row['pmt_sold_L30'] = (int) ($matchedGeneralL30->sales ?? 0);
            $row['pmt_sold_L7'] = (int) ($matchedGeneralL7->sales ?? 0);
            $row['pmt_clicks_L30'] = (int) ($matchedGeneralL30->clicks ?? 0);
            $row['pmt_clicks_L7'] = (int) ($matchedGeneralL7->clicks ?? 0);
            $row['pmt_impr_L30'] = (int) ($matchedGeneralL30->impressions ?? 0);
            $row['pmt_impr_L7'] = (int) ($matchedGeneralL7->impressions ?? 0);

            $row['SPEND_L30'] = $row['kw_spend_L30'] + $row['pmt_spend_L30'];
            $row['SPEND_L7'] = $row['kw_spend_L7'] + $row['pmt_spend_L7'];
            $row['SALES_L30'] = $row['kw_sales_L30'] + $row['pmt_sales_L30'];
            $row['SALES_L7'] = $row['kw_sales_L7'] + $row['pmt_sales_L7'];
            $row['SOLD_L30'] = $row['kw_sold_L30'] + $row['pmt_sold_L30'];
            $row['SOLD_L7'] = $row['kw_sold_L7'] + $row['pmt_sold_L7'];
            $row['CLICKS_L30'] = $row['kw_clicks_L30'] + $row['pmt_clicks_L30'];
            $row['CLICKS_L7'] = $row['kw_clicks_L7'] + $row['pmt_clicks_L7'];
            $row['IMP_L30'] = $row['kw_impr_L30'] + $row['pmt_impr_L30'];
            $row['IMP_L7'] = $row['kw_impr_L7'] + $row['pmt_impr_L7'];

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
