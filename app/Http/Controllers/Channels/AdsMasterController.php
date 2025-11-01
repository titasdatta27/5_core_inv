<?php

namespace App\Http\Controllers\Channels;

use App\Console\Commands\TiktokSheetData;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\AliExpressSheetData;
use App\Models\AmazonDatasheet;
use App\Models\AmazonDataView;
use App\Models\AmazonSbCampaignReport;
use App\Models\AmazonSpCampaignReport;
use App\Models\BestbuyUsaProduct;
use App\Models\BusinessFiveCoreSheetdata;
use App\Models\ChannelMaster;
use App\Models\DobaMetric;
use App\Models\DobaSheetdata;
use App\Models\Ebay2Metric;
use App\Models\Ebay3Metric;
use App\Models\EbayMetric;
use App\Models\FaireProductSheet;
use App\Models\FbMarketplaceSheetdata;
use App\Models\FbShopSheetdata;
use App\Models\InstagramShopSheetdata;
use App\Models\MacyProduct;
use App\Models\MarketplacePercentage;
use App\Models\MercariWoShipSheetdata;
use App\Models\MercariWShipSheetdata;
use App\Models\PLSProduct;
use App\Models\ProductMaster;
use App\Models\ReverbProduct;
use App\Models\SheinSheetData;
use App\Models\ShopifySku;
use App\Models\TemuMetric;
use App\Models\TemuProductSheet;
use App\Models\TiendamiaProduct;
use App\Models\TiktokSheet;
use App\Models\TopDawgSheetdata;
use App\Models\WaifairProductSheet;
use App\Models\WalmartMetrics;
use App\Models\EbayDataView;
use App\Models\JungleScoutProductData;
use App\Models\EbayGeneralReport;
use App\Models\EbayPriorityReport;
use App\Models\WalmartProductSheet;
use App\Models\WalmartDataView;
use App\Models\EbayThreeDataView;
use App\Models\Ebay3PriorityReport;
use App\Models\Ebay3GeneralReport;
use App\Models\WalmartCampaignReport;
use App\Models\EbayTwoDataView;
use App\Models\Ebay2GeneralReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Spatie\FlareClient\Api;

  
            

class AdsMasterController extends Controller
{
    protected $apiController;

    public function __construct(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }


    public function getAdsMasterData(Request $request)
    {
        // Fetch both channel and sheet_link from ChannelMaster
        $channels = ChannelMaster::where('status', 'Active')
            ->orderBy('id', 'asc')
            ->get(['channel', 'sheet_link', 'channel_percentage']);

        if ($channels->isEmpty()) {
            return response()->json(['status' => 404, 'message' => 'No active channel found']);
        }

        $finalData = [];

        // Map lowercase channel key => controller method
        $controllerMap = [
            'amazon'    => 'getAmazonAdRunningData',
            'ebay'      => 'getEbayChannelData',
            'ebaytwo'   => 'getEbaytwoChannelData',
            'ebaythree' => 'getEbaythreeChannelData',
            'macys'     => 'getMacysChannelData',
            'tiendamia' => 'getTiendamiaChannelData',
            'bestbuyusa' => 'getBestbuyUsaChannelData',
            'reverb'    => 'getReverbChannelData',
            'doba'      => 'getDobaChannelData',
            'temu'      => 'getTemuChannelData',
            'walmart'   => 'getWalmartChannelData',
            'pls'       => 'getPlsChannelData',
            'wayfair'   => 'getWayfairChannelData',
            'faire'     => 'getFaireChannelData',
            'shein'     => 'getSheinChannelData',
            'tiktokshop' => 'getTiktokChannelData',
            'instagramshop' => 'getInstagramChannelData',
            'aliexpress' => 'getAliexpressChannelData',
            'mercariwship' => 'getMercariWShipChannelData',
            'mercariwoship' => 'getMercariWoShipChannelData',
            'fbmarketplace' => 'getFbMarketplaceChannelData',
            'fbshop'    => 'getFbShopChannelData',
            'business5core'    => 'getBusiness5CoreChannelData',
            'topdawg'    => 'getTopDawgChannelData',
            // 'walmart' => 'getWalmartChannelData',
            // 'shopify' => 'getShopifyChannelData',
        ];

        foreach ($channels as $channelRow) {
            $channel = $channelRow->channel;

            // Base row
            $row = [
                'Channel '       => ucfirst($channel),
                'Link'           => null,
                'sheet_link'     => $channelRow->sheet_link,
                'L-60 Sales'     => 0,
                'L30 Sales'      => 0,
                'Growth'         => 0,
                'L60 Orders'     => 0,
                'L30 Orders'     => 0,
                'Gprofit%'       => 'N/A',
                'gprofitL60'     => 'N/A',
                'G Roi%'         => 'N/A',
                'G RoiL60'       => 'N/A',
                'red_margin'     => 0,
                'NR'             => 0,
                'type'           => '',
                'listed_count'   => 0,
                'W/Ads'          => 0,
                'channel_percentage' => $channelRow->channel_percentage ?? '',
                // '0 Sold SKU Count' => 0,
                // 'Sold SKU Count'   => 0,
                // 'Brand Registry'   => '',
                'Update'         => 0,
                'Account health' => null,
            ];

            // Normalize channel name for lookup
            $key = strtolower(str_replace([' ', '-', '&', '/'], '', trim($channel)));

            if (isset($controllerMap[$key]) && method_exists($this, $controllerMap[$key])) {
                $method = $controllerMap[$key];
                $data = $this->$method($request)->getData(true); // call respective function
                if (!empty($data['data'])) {
                    $row = array_merge($row, $data['data'][0]);
                }
            }

            $finalData[] = $row;
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Channel data fetched successfully',
            'data'    => $finalData,
        ]);
    }


    public function getAmazonAdRunningData(Request $request)
    {
        $result = [];

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();

        $amazonDatasheetsBySku = AmazonDatasheet::whereIn('sku', $skus)->get()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate L30 and L60 Sales from Amazon datasheets
        $l30Sales = AmazonDatasheet::whereIn('sku', $skus)->sum('units_ordered_l30');
        $l60Sales = AmazonDatasheet::whereIn('sku', $skus)->sum('units_ordered_l60');

        // If you have sales amount data, use that instead of units
        // $l30SalesAmount = AmazonDatasheet::whereIn('sku', $skus)->sum('sales_amount_l30');
        // $l60SalesAmount = AmazonDatasheet::whereIn('sku', $skus)->sum('sales_amount_l60');

        // For now, using units as placeholder - adjust based on your actual data
        $l30SalesAmount = $l30Sales;
        $l60SalesAmount = $l60Sales;

        $growth = $l30SalesAmount > 0 ? (($l30SalesAmount - $l60SalesAmount) / $l30SalesAmount) * 100 : 0;

        // Get ad campaign data
        $amazonKwL30 = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaignName', 'LIKE', '%' . $sku . '%');
                }
            })
            ->where('campaignName', 'NOT LIKE', '%PT')
            ->where('campaignName', 'NOT LIKE', '%PT.')
            ->get();

        $amazonPtL30 = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
                }
            })
            ->get();

        $amazonHlL30 = AmazonSbCampaignReport::where('ad_type', 'SPONSORED_BRANDS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
                }
            })
            ->get();

        // Calculate total ad metrics for each campaign type
        $totalSpendL30 = 0;
        $totalClicksL30 = 0;
        $totalImpressionsL30 = 0;
        $totalSoldL30 = 0;

        // Campaign type specific totals
        $kwSpendL30 = 0;
        $kwClicksL30 = 0;
        $kwImpressionsL30 = 0;
        $kwSoldL30 = 0;
        $kwSalesL30 = 0;

        $ptSpendL30 = 0;
        $ptClicksL30 = 0;
        $ptImpressionsL30 = 0;
        $ptSoldL30 = 0;
        $ptSalesL30 = 0;

        $hlSpendL30 = 0;
        $hlClicksL30 = 0;
        $hlImpressionsL30 = 0;
        $hlSoldL30 = 0;
        $hlSalesL30 = 0;

        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            $parent = $pm->parent;

            $matchedCampaignKwL30 = $amazonKwL30->first(function ($item) use ($sku) {
                return strcasecmp(trim($item->campaignName), $sku) === 0;
            });

            $matchedCampaignPtL30 = $amazonPtL30->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));
                return (
                    (str_ends_with($cleanName, $sku . ' PT') || str_ends_with($cleanName, $sku . ' PT.'))
                    && strtoupper($item->campaignStatus) === 'ENABLED'
                );
            });

            $matchedCampaignHlL30 = $amazonHlL30->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));
                $expected1 = $sku;
                $expected2 = $sku . ' HEAD';
                return ($cleanName === $expected1 || $cleanName === $expected2)
                    && strtoupper($item->campaignStatus) === 'ENABLED';
            });

            // KW Campaign metrics
            $kwSpendL30 += $matchedCampaignKwL30->spend ?? 0;
            $kwClicksL30 += $matchedCampaignKwL30->clicks ?? 0;
            $kwImpressionsL30 += $matchedCampaignKwL30->impressions ?? 0;
            $kwSoldL30 += $matchedCampaignKwL30->unitsSoldClicks30d ?? 0;
            $kwSalesL30 += $matchedCampaignKwL30->sales30d ?? 0;

            // PT Campaign metrics
            $ptSpendL30 += $matchedCampaignPtL30->spend ?? 0;
            $ptClicksL30 += $matchedCampaignPtL30->clicks ?? 0;
            $ptImpressionsL30 += $matchedCampaignPtL30->impressions ?? 0;
            $ptSoldL30 += $matchedCampaignPtL30->unitsSoldClicks30d ?? 0;
            $ptSalesL30 += $matchedCampaignPtL30->sales30d ?? 0;

            // HL Campaign metrics
            $hlSpendL30 += $matchedCampaignHlL30->cost ?? 0;
            $hlClicksL30 += $matchedCampaignHlL30->clicks ?? 0;
            $hlImpressionsL30 += $matchedCampaignHlL30->impressions ?? 0;
            $hlSoldL30 += $matchedCampaignHlL30->unitsSold ?? 0;
            $hlSalesL30 += $matchedCampaignHlL30->sales ?? 0;

            // Add to totals
            $totalSpendL30 += ($matchedCampaignKwL30->spend ?? 0) +
                ($matchedCampaignPtL30->spend ?? 0) +
                ($matchedCampaignHlL30->cost ?? 0);

            $totalClicksL30 += ($matchedCampaignKwL30->clicks ?? 0) +
                ($matchedCampaignPtL30->clicks ?? 0) +
                ($matchedCampaignHlL30->clicks ?? 0);

            $totalImpressionsL30 += ($matchedCampaignKwL30->impressions ?? 0) +
                ($matchedCampaignPtL30->impressions ?? 0) +
                ($matchedCampaignHlL30->impressions ?? 0);

            $totalSoldL30 += ($matchedCampaignKwL30->unitsSoldClicks30d ?? 0) +
                ($matchedCampaignPtL30->unitsSoldClicks30d ?? 0) +
                ($matchedCampaignHlL30->unitsSold ?? 0);
        }

        // Get Amazon marketing percentage
        $percentage = ChannelMaster::where('channel', 'Amazon')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100;

        // Calculate profit (you'll need to adjust this based on your actual profit calculation)
        $totalProfit = $l30SalesAmount * 0.2; // Placeholder - 20% profit margin
        $totalProfitL60 = $l60SalesAmount * 0.2;

        $gProfitPct = $l30SalesAmount > 0 ? ($totalProfit / $l30SalesAmount) * 100 : 0;
        $gprofitL60 = $l60SalesAmount > 0 ? ($totalProfitL60 / $l60SalesAmount) * 100 : 0;

        // Calculate ROI
        $gRoi = $totalSpendL30 > 0 ? ($totalProfit / $totalSpendL30) * 100 : 0;
        $gRoiL60 = $totalSpendL30 > 0 ? ($totalProfitL60 / $totalSpendL30) * 100 : 0;

        // Calculate ACOS for each campaign type
        $kwAcos = $kwSalesL30 > 0 ? ($kwSpendL30 / $kwSalesL30) * 100 : 0;
        $ptAcos = $ptSalesL30 > 0 ? ($ptSpendL30 / $ptSalesL30) * 100 : 0;
        $hlAcos = $hlSalesL30 > 0 ? ($hlSpendL30 / $hlSalesL30) * 100 : 0;
        $totalAcos = $l30SalesAmount > 0 ? ($totalSpendL30 / $l30SalesAmount) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Amazon')->first();

        $result[] = [
            'Channel '   => 'Amazon',
            'Link'       => null,
            'sheet_link' => $channelData->sheet_link ?? '',
            'L-60 Sales' => intval($l60SalesAmount),
            'L30 Sales'  => intval($l30SalesAmount),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Sales, // Using sales units as orders
            'L30 Orders' => $l30Sales, // Using sales units as orders
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi%'     => round($gRoi, 2) . '%',
            'G RoiL60'   => round($gRoiL60, 2) . '%',
            'red_margin' => 0,
            'NR'         => $channelData->nr ?? 0,
            'type'       => $channelData->type ?? '',
            'listed_count' => count($skus),
            'W/Ads'      => $channelData->w_ads ?? 0,
            'channel_percentage' => $channelData->channel_percentage ?? '',
            'Update'     => $channelData->update ?? 0,
            'Account health' => null,

            // Total Ad metrics
            'SPEND_L30'  => round($totalSpendL30, 2),
            'CLICKS_L30' => $totalClicksL30,
            'IMPRESSIONS_L30' => $totalImpressionsL30,
            'SOLD_L30'   => $totalSoldL30,

            // KW Campaign metrics
            'KW_SPEND_L30' => round($kwSpendL30, 2),
            'KW_CLICKS_L30' => $kwClicksL30,
            'KW_IMPRESSIONS_L30' => $kwImpressionsL30,
            'KW_SOLD_L30' => $kwSoldL30,
            'KW_SALES_L30' => round($kwSalesL30, 2),
            'KW_ACOS' => round($kwAcos, 2) . '%',

            // PT Campaign metrics
            'PT_SPEND_L30' => round($ptSpendL30, 2),
            'PT_CLICKS_L30' => $ptClicksL30,
            'PT_IMPRESSIONS_L30' => $ptImpressionsL30,
            'PT_SOLD_L30' => $ptSoldL30,
            'PT_SALES_L30' => round($ptSalesL30, 2),
            'PT_ACOS' => round($ptAcos, 2) . '%',

            // HL Campaign metrics
            'HL_SPEND_L30' => round($hlSpendL30, 2),
            'HL_CLICKS_L30' => $hlClicksL30,
            'HL_IMPRESSIONS_L30' => $hlImpressionsL30,
            'HL_SOLD_L30' => $hlSoldL30,
            'HL_SALES_L30' => round($hlSalesL30, 2),
            'HL_ACOS' => round($hlAcos, 2) . '%',

            // Combined metrics
            'Ad Sales'   => intval($l30SalesAmount),
            'Ad Sold'    => $l30Sales,
            'ACOS'       => round($totalAcos, 2) . '%',
            'Tacos'      => 'N/A',
            'Pft'        => round($totalProfit, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Amazon channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getEbayChannelData(Request $request)
    {
        $result = [];

        $query = EbayMetric::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('ebay_l30');
        $l60Orders = $query->sum('ebay_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(ebay_l30 * ebay_price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(ebay_l60 * ebay_price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'eBay')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'ebay_price', 'ebay_l30', 'ebay_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->ebay_price;
            $unitsL30  = (int) $row->ebay_l30;
            $unitsL60  = (int) $row->ebay_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60       = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'eBay')->first();

        $result[] = [
            'Channel '   => 'eBay',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'eBay channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getEbaytwoChannelData(Request $request)
    {
        $result = [];

        // $query = Ebay2Metric::where('sku', 'not like', '%Parent%');

        $query = DB::connection('apicentral')
            ->table('ebay2_metrics')
            ->where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('ebay_l30');
        $l60Orders = $query->sum('ebay_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(ebay_l30 * ebay_price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(ebay_l60 * ebay_price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'EbayTwo')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'ebay_price', 'ebay_l30', 'ebay_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->ebay_price;
            $unitsL30  = (int) $row->ebay_l30;
            $unitsL60  = (int) $row->ebay_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'EbayTwo')->first();

        $result[] = [
            'Channel '   => 'EbayTwo',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'eBay2 channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getEbaythreeChannelData(Request $request)
    {
        $result = [];

        $query = Ebay3Metric::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('ebay_l30');
        $l60Orders = $query->sum('ebay_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(ebay_l30 * ebay_price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(ebay_l60 * ebay_price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'EbayThree')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'ebay_price', 'ebay_l30', 'ebay_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->ebay_price;
            $unitsL30  = (int) $row->ebay_l30;
            $unitsL60  = (int) $row->ebay_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'EbayThree')->first();

        $result[] = [
            'Channel '   => 'EbayThree',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'eBay three channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getMacysChannelData(Request $request)
    {
        $result = [];

        $query = MacyProduct::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('m_l30');
        $l60Orders = $query->sum('m_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(m_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(m_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Macys')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'm_l30', 'm_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->m_l30;
            $unitsL60  = (int) $row->m_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60       = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Macys')->first();

        $result[] = [
            'Channel '   => 'Macys',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Macys channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getReverbChannelData(Request $request)
    {
        $result = [];

        $query = ReverbProduct::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('r_l30');
        $l60Orders = $query->sum('r_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(r_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(r_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Reverb')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'r_l30', 'r_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->r_l30;
            $unitsL60  = (int) $row->r_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Reverb')->first();

        $result[] = [
            'Channel '   => 'Reverb',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Reverb channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getDobaChannelData(Request $request)
    {
        $result = [];

        $query = DobaSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Doba')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Doba')->first();

        $result[] = [
            'Channel '   => 'Doba',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Doba channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getTemuChannelData(Request $request)
    {
        $result = [];

        $query = TemuMetric::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('quantity_purchased_l30');
        $l60Orders = $query->sum('quantity_purchased_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(quantity_purchased_l30 * temu_sheet_price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(quantity_purchased_l60 * temu_sheet_price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Temu')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'temu_sheet_price', 'quantity_purchased_l30', 'quantity_purchased_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->temu_sheet_price;
            $unitsL30  = (int) $row->quantity_purchased_l30;
            $unitsL60  = (int) $row->quantity_purchased_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['temu_ship']) ? (float) $values['temu_ship'] : ($pm->temu_ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60       = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Temu')->first();

        $result[] = [
            'Channel '   => 'Temu',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Doba channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getWalmartChannelData(Request $request)
    {
        $result = [];

        $query = WalmartMetrics::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get Walmart marketing percentage
        $percentage = ChannelMaster::where('channel', 'Walmart')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;

        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Walmart')->first();

        $result[] = [
            'Channel '   => 'Walmart',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Walmart channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getTiendamiaChannelData(Request $request)
    {
        $result = [];

        $query = TiendamiaProduct::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('m_l30');
        $l60Orders = $query->sum('m_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(m_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(m_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Tiendamia')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'm_l30', 'm_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->m_l30;
            $unitsL60  = (int) $row->m_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Tiendamia')->first();

        $result[] = [
            'Channel '   => 'Tiendamia',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Tiendamia channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getBestbuyUsaChannelData(Request $request)
    {
        $result = [];

        $query = BestbuyUsaProduct::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('m_l30');
        $l60Orders = $query->sum('m_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(m_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(m_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'BestBuy USA')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'm_l30', 'm_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->m_l30;
            $unitsL60  = (int) $row->m_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'BestBuy USA')->first();

        $result[] = [
            'Channel '   => 'BestBuy USA',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'Bestbuy USA channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getPlsChannelData(Request $request)
    {
        $result = [];

        $query = PLSProduct::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('p_l30');
        $l60Orders = $query->sum('p_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(p_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(p_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'PLS')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'p_l30', 'p_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->p_l30;
            $unitsL60  = (int) $row->p_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'PLS')->first();

        $result[] = [
            'Channel '   => 'PLS',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60'   => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'      => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'PLS channel data fetched successfully',
            'data' => $result,
        ]);
    }


    public function getWayfairChannelData(Request $request)
    {
        $result = [];

        $query = WaifairProductSheet::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Wayfair')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Wayfair')->first();

        $result[] = [
            'Channel '   => 'Wayfair',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getFaireChannelData(Request $request)
    {
        $result = [];

        $query = FaireProductSheet::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('f_l30');
        $l60Orders = $query->sum('f_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(f_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(f_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Faire')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'f_l30', 'f_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->f_l30;
            $unitsL60  = (int) $row->f_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Faire')->first();

        $result[] = [
            'Channel '   => 'Faire',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getSheinChannelData(Request $request)
    {
        $result = [];

        $query = SheinSheetData::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('shopify_sheinl30');
        $l60Orders = $query->sum('shopify_sheinl60');

        $l30Sales  = (clone $query)->selectRaw('SUM(shopify_sheinl30 * shopify_price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(shopify_sheinl60 * shopify_price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Shein')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'shopify_price', 'shopify_sheinl30', 'shopify_sheinl60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->shopify_price;
            $unitsL30  = (int) $row->shopify_sheinl30;
            $unitsL60  = (int) $row->shopify_sheinl60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Shein')->first();

        $result[] = [
            'Channel '   => 'Shein',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getTiktokChannelData(Request $request)
    {
        $result = [];

        $query = TiktokSheet::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Tiktok Shop')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Tiktok Shop')->first();

        $result[] = [
            'Channel '   => 'Tiktok Shop',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getInstagramChannelData(Request $request)
    {
        $result = [];

        $query = InstagramShopSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('i_l30');
        $l60Orders = $query->sum('i_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(i_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(i_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Instagram Shop')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'i_l30', 'i_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->i_l30;
            $unitsL60  = (int) $row->i_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Instagram Shop')->first();

        $result[] = [
            'Channel '   => 'Instagram Shop',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getAliexpressChannelData(Request $request)
    {
        $result = [];

        $query = AliExpressSheetData::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('aliexpress_l30');
        $l60Orders = $query->sum('aliexpress_l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(aliexpress_l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(aliexpress_l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Aliexpress')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'aliexpress_l30', 'aliexpress_l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->aliexpress_l30;
            $unitsL60  = (int) $row->aliexpress_l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Aliexpress')->first();

        $result[] = [
            'Channel '   => 'Aliexpress',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getMercariWShipChannelData(Request $request)
    {
        $result = [];

        $query = MercariWShipSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Mercari w ship')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Mercari w ship')->first();

        $result[] = [
            'Channel '   => 'Mercari w ship',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getMercariWoShipChannelData(Request $request)
    {
        $result = [];

        $query = MercariWoShipSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Mercari wo ship')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Mercari wo ship')->first();

        $result[] = [
            'Channel '   => 'Mercari wo ship',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getFbMarketplaceChannelData(Request $request)
    {
        $result = [];

        $query = FbMarketplaceSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'FB Marketplace')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'FB Marketplace')->first();

        $result[] = [
            'Channel '   => 'FB Marketplace',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getFbShopChannelData(Request $request)
    {
        $result = [];

        $query = FbShopSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'FB Shop')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'FB Shop')->first();

        $result[] = [
            'Channel '   => 'FB Shop',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getBusiness5CoreChannelData(Request $request)
    {
        $result = [];

        $query = BusinessFiveCoreSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'Business 5Core')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'Business 5Core')->first();

        $result[] = [
            'Channel '   => 'Business 5Core',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }

    public function getTopDawgChannelData(Request $request)
    {
        $result = [];

        $query = TopDawgSheetdata::where('sku', 'not like', '%Parent%');

        $l30Orders = $query->sum('l30');
        $l60Orders = $query->sum('l60');

        $l30Sales  = (clone $query)->selectRaw('SUM(l30 * price) as total')->value('total') ?? 0;
        $l60Sales  = (clone $query)->selectRaw('SUM(l60 * price) as total')->value('total') ?? 0;

        $growth = $l30Sales > 0 ? (($l30Sales - $l60Sales) / $l30Sales) * 100 : 0;

        // Get eBay marketing percentage
        $percentage = ChannelMaster::where('channel', 'TopDawg')->value('channel_percentage') ?? 100;
        $percentage = $percentage / 100; // convert % to fraction

        // Load product masters (lp, ship) keyed by SKU
        $productMasters = ProductMaster::all()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        // Calculate total profit
        $ebayRows     = $query->get(['sku', 'price', 'l30', 'l60']);
        $totalProfit  = 0;
        $totalProfitL60  = 0;
        $totalCogs       = 0;
        $totalCogsL60    = 0;


        foreach ($ebayRows as $row) {
            $sku       = strtoupper($row->sku);
            $price     = (float) $row->price;
            $unitsL30  = (int) $row->l30;
            $unitsL60  = (int) $row->l60;

            $soldAmount = $unitsL30 * $price;
            if ($soldAmount <= 0) {
                continue;
            }

            $lp   = 0;
            $ship = 0;

            if (isset($productMasters[$sku])) {
                $pm = $productMasters[$sku];

                $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
                $ship = isset($values['ship']) ? (float) $values['ship'] : ($pm->ship ?? 0);
            }

            // Profit per unit
            $profitPerUnit = ($price * $percentage) - $lp - $ship;
            $profitTotal   = $profitPerUnit * $unitsL30;
            $profitTotalL60   = $profitPerUnit * $unitsL60;

            $totalProfit += $profitTotal;
            $totalProfitL60 += $profitTotalL60;

            $totalCogs    += ($unitsL30 * $lp);
            $totalCogsL60 += ($unitsL60 * $lp);
        }

        // --- FIX: Calculate total LP only for SKUs in eBayMetrics ---
        $ebaySkus   = $ebayRows->pluck('sku')->map(fn($s) => strtoupper($s))->toArray();
        $ebayPMs    = ProductMaster::whereIn('sku', $ebaySkus)->get();

        $totalLpValue = 0;
        foreach ($ebayPMs as $pm) {
            $values = is_array($pm->Values) ? $pm->Values : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

            $lp = isset($values['lp']) ? (float) $values['lp'] : ($pm->lp ?? 0);
            $totalLpValue += $lp;
        }

        // Use L30 Sales for denominator
        $gProfitPct = $l30Sales > 0 ? ($totalProfit / $l30Sales) * 100 : 0;
        $gprofitL60 = $l60Sales > 0 ? ($totalProfitL60 / $l60Sales) * 100 : 0;

        // $gRoi       = $totalLpValue > 0 ? ($totalProfit / $totalLpValue) : 0;
        // $gRoiL60    = $totalLpValue > 0 ? ($totalProfitL60 / $totalLpValue) : 0;

        $gRoi    = $totalCogs > 0 ? ($totalProfit / $totalCogs) * 100 : 0;
        $gRoiL60 = $totalCogsL60 > 0 ? ($totalProfitL60 / $totalCogsL60) * 100 : 0;

        // Channel data
        $channelData = ChannelMaster::where('channel', 'TopDawg')->first();

        $result[] = [
            'Channel '   => 'TopDawg',
            'L-60 Sales' => intval($l60Sales),
            'L30 Sales'  => intval($l30Sales),
            'Growth'     => round($growth, 2) . '%',
            'L60 Orders' => $l60Orders,
            'L30 Orders' => $l30Orders,
            'Gprofit%'   => round($gProfitPct, 2) . '%',
            'gprofitL60' => round($gprofitL60, 2) . '%',
            'G Roi'      => round($gRoi, 2),
            'G RoiL60'   => round($gRoiL60, 2),
            'type'       => $channelData->type ?? '',
            'W/Ads'      => $channelData->w_ads ?? 0,
            'NR'         => $channelData->nr ?? 0,
            'Update'     => $channelData->update ?? 0,
            'cogs'       => round($totalCogs, 2),
        ];

        return response()->json([
            'status' => 200,
            'message' => 'wayfair channel data fetched successfully',
            'data' => $result,
        ]);
    }



    /**
     * Store a newly created channel in storage.
     */
    public function store(Request $request)
    {
        // Validate Request Data
        $validatedData = $request->validate([
            'channel' => 'required|string',
            'sheet_link' => 'nullable|url',
            'type' => 'nullable|string',
            'channel_percentage' => 'nullable|numeric',
            // 'status' => 'required|in:Active,In Active,To Onboard,In Progress',
            // 'executive' => 'nullable|string',
            // 'b_link' => 'nullable|string',
            // 's_link' => 'nullable|string',
            // 'user_id' => 'nullable|string',
            // 'action_req' => 'nullable|string',
        ]);
        // Save Data to Database
        try {
            $channel = ChannelMaster::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'channel saved successfully',
                'data' => $channel
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving channel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save channel. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a update channel in storage.
     */
    public function update(Request $request)
    {
        $originalChannel = $request->input('original_channel');
        $updatedChannel = $request->input('channel');
        $sheetUrl = $request->input('sheet_url');
        $type = $request->input('type');
        $channelPercentage = $request->input('channel_percentage');

        $channel = ChannelMaster::where('channel', $originalChannel)->first();

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Channel not found']);
        }

        $channel->channel = $updatedChannel;
        $channel->sheet_link = $sheetUrl;
        $channel->type = $type;
        $channel->channel_percentage = $channelPercentage;
        $channel->save();

        return response()->json(['success' => true]);
    }


    public function getChannelCounts()
    {
        // Fetch counts from the database
        $totalChannels = DB::table('channel_master')->count();
        $activeChannels = DB::table('channel_master')->where('status', 'Active')->count();
        $inactiveChannels = DB::table('channel_master')->where('status', 'In Active')->count();

        return response()->json([
            'success' => true,
            'totalChannels' => $totalChannels,
            'activeChannels' => $activeChannels,
            'inactiveChannels' => $inactiveChannels,
        ]);
    }

    public function destroy(Request $request)
    {
        // Delete channel from database
    }

    public function sendToGoogleSheet(Request $request)
    {

        $channel = $request->input('channel');
        $checked = $request->input('checked');

        Log::info('Received update-checkbox request', [
            'channel' => $channel,
            'checked' => $checked,
        ]);

        // Log for debugging
        Log::info("Updating GSheet for channel: $channel, checked: " . ($checked ? 'true' : 'false'));

        $url = 'https://script.google.com/macros/s/AKfycbzhlu7KV3dx3PS-9XPFBI9FMgI0JZIAgsuZY48Lchr_60gkSmx1hNAukKwFGZXgPwid/exec';

        $response = Http::post($url, [
            'channel' => $channel,
            'checked' => $checked
        ]);

        if ($response->successful()) {
            Log::info("Google Sheet updated successfully");
            return response()->json(['success' => true, 'message' => 'Updated GSheet']);
        } else {
            Log::error('Failed to send to GSheet:', [$response->body()]);
            return response()->json(['success' => false, 'message' => 'Failed to update GSheet'], 500);
        }
    }

    public function updateExecutive(Request $request)
    {
        $channel = trim($request->input('channel'));
        $exec = trim($request->input('exec'));

        $spreadsheetId = '13ZjGtJvSkiLHin2VnkBD-hrGimSRD7duVjILfkoJ2TA';
        $url = 'https://script.google.com/macros/s/AKfycbzYct_htZ_z89S36bPMDdjdDy6s1Nrzm79No6N2PqPriyrwXF1plIschk1c4cDnPYQ5/exec'; // Your Apps Script doPost URL

        $payload = [
            'channel' => $channel,
            'exec' => $exec,
            'action' => 'update_exec'
        ];

        $response = Http::post($url, $payload);

        if ($response->successful()) {
            return response()->json(['message' => 'Executive updated successfully.']);
        } else {
            return response()->json(['message' => 'Failed to update.'], 500);
        }
    }


    public function updateSheetLink(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'sheet_link' => 'nullable|url',
        ]);

        ChannelMaster::updateOrCreate(
            ['channel' => $request->channel], // search by channel
            ['sheet_link' => $request->sheet_link] // update or insert
        );

        return response()->json(['status' => 'success']);
    }

    public function toggleCheckboxFlag(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'field' => 'required|in:nr,w_ads,update',
            'value' => 'required|boolean'
        ]);

        $channelName = trim($request->channel);
        $field = $request->field;
        $value = $request->value;

        $channel = ChannelMaster::whereRaw('LOWER(channel) = ?', [strtolower($channelName)])->first();

        if ($channel) {
            $channel->$field = $value;
            $channel->save();
            return response()->json(['success' => true, 'message' => 'Channel updated.']);
        }

        // Channel not found — insert new row
        $newChannel = new ChannelMaster();
        $newChannel->channel = $channelName;
        $newChannel->$field = $value;
        $newChannel->save();

        return response()->json(['success' => true, 'message' => 'New channel inserted and updated.']);
    }


    public function updateType(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'type' => 'nullable|string'
        ]);

        $channelName = trim($request->input('channel'));
        $type = $request->input('type');

        $channel = ChannelMaster::where('channel', $channelName)->first();

        if (!$channel) {
            // If not found, create new
            $channel = new ChannelMaster();
            $channel->channel = $channelName;
        }

        $channel->type = $type;
        $channel->save();

        return response()->json([
            'success' => true,
            'message' => 'Type updated successfully.'
        ]);
    }

    public function updatePercentage(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'channel_percentage' => 'nullable|numeric'
        ]);

        $channelName = trim($request->input('channel'));
        $channelPercentage = $request->input('channel_percentage');

        $channel = ChannelMaster::where('channel', $channelName)->first();

        if (!$channel) {
            // If not found, create new
            $channel = new ChannelMaster();
            $channel->channel = $channelName;
        }

        $channel->channel_percentage = $channelPercentage;
        $channel->save();

        return response()->json([
            'success' => true,
            'message' => 'Channel percentage updated successfully.'
        ]);
    }


    public function getSalesTrendData()
    {
        $today = now();
        $l30Start = $today->copy()->subDays(30);
        $l60Start = $today->copy()->subDays(60);

        // Get daily sales for last 60 days
        $salesData = DB::connection('apicentral')
            ->table('shopify_order_items')
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(quantity * price) as total_sales')
            )
            ->where('order_date', '>=', $l60Start)
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date', 'asc')
            ->get();

        // Split into two datasets (L30 & L60)
        $l30Data = [];
        $l60Data = [];
        foreach ($salesData as $row) {
            $date = Carbon::parse($row->date)->format('Y-m-d');
            if ($row->date >= $l30Start->toDateString()) {
                $l30Data[$date] = $row->total_sales;
            } else {
                $l60Data[$date] = $row->total_sales;
            }
        }

        // Prepare consistent date series
        $period = new \DatePeriod(
            $l60Start,
            new \DateInterval('P1D'),
            $today
        );

        $chartData = [];
        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            $chartData[$formatted] = [
                'date' => $formatted,
                'l30_sales' => $l30Data[$formatted] ?? 0,
                'l60_sales' => $l60Data[$formatted] ?? 0,
                'gprofit' => 0, // initialize
            ];
        }

        // Load product_master LP & SHIP
        $productMasters = ProductMaster::all()->keyBy(fn($item) => strtoupper($item->sku));

        // Get order items for last 30 days (L30)
        $orderItems = DB::connection('apicentral')
            ->table('shopify_order_items')
            ->select('sku', 'quantity', 'price', 'order_date')
            ->where('order_date', '>=', $l30Start)
            ->get();

        if ($orderItems->isNotEmpty()) {
            $dailySales = [];
            $dailyProfit = [];

            foreach ($orderItems as $item) {
                $sku = strtoupper(trim($item->sku));
                $date = Carbon::parse($item->order_date)->format('Y-m-d');
                $qty = (int) $item->quantity;
                $price = (float) $item->price;

                $lp = 0;
                $ship = 0;
                if (isset($productMasters[$sku])) {
                    $pm = $productMasters[$sku];
                    $values = is_array($pm->Values)
                        ? $pm->Values
                        : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);
                    $lp = $values['lp'] ?? $pm->lp ?? 0;
                    $ship = $values['ship'] ?? $pm->ship ?? 0;
                }

                $sales = $qty * $price;
                $profit = ($price - $lp - $ship) * $qty;

                $dailySales[$date] = ($dailySales[$date] ?? 0) + $sales;
                $dailyProfit[$date] = ($dailyProfit[$date] ?? 0) + $profit;
            }

            // Assign GProfit per day
            foreach ($chartData as $date => &$row) {
                $sales = $dailySales[$date] ?? 0;
                $profit = $dailyProfit[$date] ?? 0;
                $row['gprofit'] = $sales > 0 ? round(($profit / $sales) * 100, 2) : 0;
            }
        }

        // Convert chartData to indexed array for JSON
        $chartData = array_values($chartData);

        // Optional: summary for total L30
        $totalSalesL30 = array_sum($dailySales ?? []);
        $totalProfitL30 = array_sum($dailyProfit ?? []);
        $totalGProfit = $totalSalesL30 > 0 ? ($totalProfitL30 / $totalSalesL30) * 100 : 0;

        return response()->json([
            'chartData' => $chartData,
            'summary' => [
                'total_sales_l30' => round($totalSalesL30, 2),
                'total_profit_l30' => round($totalProfitL30, 2),
                'gprofit' => round($totalGProfit, 2),
            ],
        ]);
    }

    public function channelAdsMaster(Request $request)
    {
        $mode = $request->query('mode');
        $demo = $request->query('demo');
        // return view($first, compact('mode', 'demo', 'second', 'channels'));
        return view('channels.ads-masters', [
            'mode' => $mode,
            'demo' => $demo,
        ]);
    }

    public function channelAdvMaster(Request $request)
    {
        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();
        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();

        $amazonKwL30 = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) $q->orWhere('campaignName', 'LIKE', '%' . $sku . '%');
            })
            ->where('campaignName', 'NOT LIKE', '%PT')
            ->where('campaignName', 'NOT LIKE', '%PT.')
            ->get();

        $amazonPtL30 = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
            })
            ->get();

        $amazonHlL30 = AmazonSbCampaignReport::where('ad_type', 'SPONSORED_BRANDS')
            ->where('report_date_range', 'L30')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
            })
            ->get();

        $amazonHlL7 = AmazonSbCampaignReport::where('ad_type', 'SPONSORED_BRANDS')
            ->where('report_date_range', 'L7')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
            })
            ->get();

        $parentSkuCounts = $productMasters
            ->filter(fn($pm) => $pm->parent && !str_starts_with(strtoupper($pm->sku), 'PARENT'))
            ->groupBy('parent')
            ->map->count();


        $result = [];
        $parentHlSpendData = [];
        
        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            $parent = trim($pm->parent);

            $matchedCampaignHlL30 = $amazonHlL30->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));
                return in_array($cleanName, [$sku, $sku . ' HEAD']) && strtoupper($item->campaignStatus) === 'ENABLED';
            });
            $matchedCampaignHlL7 = $amazonHlL7->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));
                return in_array($cleanName, [$sku, $sku . ' HEAD']) && strtoupper($item->campaignStatus) === 'ENABLED';
            });

            if (str_starts_with($sku, 'PARENT')) {
                $childCount = $parentSkuCounts[$parent] ?? 0;
                $parentHlSpendData[$parent] = [
                    'total_L30' => $matchedCampaignHlL30->cost ?? 0,
                    'total_L7'  => $matchedCampaignHlL7->cost ?? 0,
                    'total_L30_sales' => $matchedCampaignHlL30->sales ?? 0,
                    'total_L7_sales'  => $matchedCampaignHlL7->sales ?? 0,
                    'total_L30_sold'  => $matchedCampaignHlL30->unitsSold ?? 0,
                    'total_L7_sold'   => $matchedCampaignHlL7->unitsSold ?? 0,
                    'total_L30_impr'  => $matchedCampaignHlL30->impressions ?? 0,
                    'total_L7_impr'   => $matchedCampaignHlL7->impressions ?? 0,
                    'total_L30_clicks'=> $matchedCampaignHlL30->clicks ?? 0,
                    'total_L7_clicks' => $matchedCampaignHlL7->clicks ?? 0,
                    'childCount'=> $childCount,
                ];
            }
        }

        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            $parent = trim($pm->parent);

            $matchedCampaignKwL30 = $amazonKwL30->first(function ($item) use ($sku) {
                $campaignName = strtoupper(trim(rtrim($item->campaignName, '.')));
                $cleanSku = strtoupper(trim(rtrim($sku, '.')));
                return $campaignName === $cleanSku;
            });

            $matchedCampaignPtL30 = $amazonPtL30->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));
                return (str_ends_with($cleanName, $sku . ' PT') || str_ends_with($cleanName, $sku . ' PT.'))
                    && strtoupper($item->campaignStatus) === 'ENABLED';
            });

            $row = [];
            $row['parent'] = $parent;
            $row['sku'] = $pm->sku;

            $row['kw_clicks_L30'] = $matchedCampaignKwL30->clicks ?? 0;
            $row['kw_spend_L30']  = $matchedCampaignKwL30->spend ?? 0;
            $row['kw_sales_L30']  = $matchedCampaignKwL30->sales30d ?? 0;
            $row['kw_sold_L30']  = $matchedCampaignKwL30->unitsSoldSameSku30d ?? 0;
            
            $row['pt_clicks_L30'] = $matchedCampaignPtL30->clicks ?? 0;
            $row['pt_spend_L30']  = $matchedCampaignPtL30->spend ?? 0;
            $row['pt_sales_L30']  = $matchedCampaignPtL30->sales30d ?? 0;
            $row['pt_sold_L30']  = $matchedCampaignPtL30->unitsSoldSameSku30d ?? 0;
            $row['hl_clicks_L30'] = $matchedCampaignHlL30->clicks ?? 0;
            $row['hl_sales_L30']  = 0;
            $row['hl_sold_L30']  = 0;

            if (str_starts_with($sku, 'PARENT')) {
                $row['hl_spend_L30'] = $matchedCampaignHlL30->cost ?? 0;
                $row['hl_sales_L30']  = $matchedCampaignHlL30->sales ?? 0;
                $row['hl_sold_L30']  = $matchedCampaignHlL30->unitsSold ?? 0;
                $row['hl_clicks_L30'] = $matchedCampaignHlL30->clicks ?? 0;
            } 
            elseif (isset($parentHlSpendData[$parent]) && $parentHlSpendData[$parent]['childCount'] > 0) {
                $row['hl_spend_L30'] = $parentHlSpendData[$parent]['total_L30'] / $parentHlSpendData[$parent]['childCount'];
                $row['hl_sales_L30']  = $parentHlSpendData[$parent]['total_L30_sales'] / $parentHlSpendData[$parent]['childCount'];
                $row['hl_sold_L30']  = $parentHlSpendData[$parent]['total_L30_sold'] / $parentHlSpendData[$parent]['childCount'];
                $row['hl_clicks_L30'] = $parentHlSpendData[$parent]['total_L30_clicks'] / $parentHlSpendData[$parent]['childCount'];
            } else {
                $row['hl_spend_L30'] = 0;
                $row['hl_sales_L30'] = 0;
                $row['hl_sold_L30']  = 0;
                $row['hl_clicks_L30'] = 0;
            }
            
            $childCount = $parentSkuCounts[$parent] ?? 0;
            $childCount = max($childCount, 1);
            $hl_share_clicks_L30 = ($matchedCampaignHlL30->impressions ?? 0) / $childCount;
            $row['CLICKS_L30'] = ($row['pt_clicks_L30'] + $row['kw_clicks_L30'] + $hl_share_clicks_L30);
            $row['SPEND_L30'] = $row['pt_spend_L30'] + $row['kw_spend_L30'] + $row['hl_spend_L30'];
            $row['SALES_L30'] = $row['pt_sales_L30'] + $row['kw_sales_L30'] + $row['hl_sales_L30'];
            $row['SOLD_L30'] = $row['pt_sold_L30'] + $row['kw_sold_L30'] + $row['hl_sold_L30'];
            $result[] = $row;
        }

        $metrics = [
            'SPEND_L30', 'kw_spend_L30', 'pt_spend_L30', 'hl_spend_L30',
            'CLICKS_L30', 'kw_clicks_L30', 'pt_clicks_L30', 'hl_clicks_L30',
            'SOLD_L30', 'kw_sold_L30', 'pt_sold_L30', 'hl_sold_L30',
            'SALES_L30', 'kw_sales_L30', 'pt_sales_L30', 'hl_sales_L30',
        ];

        $totals = array_fill_keys($metrics, 0.0);
        foreach ($result as $row) {
            $sku = strtolower(trim($row['sku'] ?? ''));
            if (strpos($sku, 'parent ') !== false) continue;

            foreach ($metrics as $key) {
                $val = $row[$key] ?? 0;
                $totals[$key] += is_numeric($val) ? $val + 0.0 : 0.0;
            }
        }
        $SPEND_L30_total = $totals['SPEND_L30'];
        $kw_spend_L30_total = $totals['kw_spend_L30'];
        $pt_spend_L30_total = $totals['pt_spend_L30'];
        $hl_spend_L30_total = $totals['hl_spend_L30'];
        $CLICKS_L30_total = $totals['CLICKS_L30'];
        $kw_clicks_L30_total = $totals['kw_clicks_L30'];
        $pt_clicks_L30_total = $totals['pt_clicks_L30'];
        $hl_clicks_L30_total = $totals['hl_clicks_L30'];
        $SOLD_L30_Total = $totals['SOLD_L30'];
        $kw_sold_L30_Total = $totals['kw_sold_L30'];
        $pt_sold_L30_Total = $totals['pt_sold_L30'];
        $hl_sold_L30_Total = $totals['hl_sold_L30'];
        $SALES_L30_Total = $totals['SALES_L30'];
        $kw_sales_L30_Total = $totals['kw_sales_L30'];
        $pt_sales_L30_Total = $totals['pt_sales_L30'];
        $hl_sales_L30_Total = $totals['hl_sales_L30'];

        /** Start Ebay Code **/

        $normalizeSku = fn($sku) => strtoupper(trim($sku));
        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));

        $ebayMetricData = DB::connection('apicentral')->table('ebay_one_metrics')
            ->select('sku', 'ebay_price', 'item_id')
            ->whereIn('sku', $skus)
            ->get()
            ->keyBy(fn($item) => $normalizeSku($item->sku));

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

        $itemIds = $ebayMetricData->pluck('item_id')->toArray();
        $ebayGeneralReportsL30 = EbayGeneralReport::where('report_range', 'L30')
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
            $row = [];

            $row['parent'] = $parent;
            $row['sku'] = $pm->sku;
            $row['campaignName'] = $matchedCampaignL7->campaign_name ?? ($matchedCampaignL30->campaign_name ?? '');
            $row['kw_spend_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_ad_fees_payout_currency ?? 0);
            $row['kw_sales_L30'] = (float) str_replace('USD ', '', $matchedCampaignL30->cpc_sale_amount_payout_currency ?? 0);
            $row['kw_sold_L30'] = (int) ($matchedCampaignL30->cpc_attributed_sales ?? 0);
            $row['kw_clicks_L30'] = (int) ($matchedCampaignL30?->cpc_clicks ?? 0);
            $row['pmt_spend_L30'] = (float) str_replace('USD ', '', $matchedGeneralL30->ad_fees ?? 0);
            $row['pmt_sales_L30'] = (float) str_replace('USD ', '', $matchedGeneralL30->sale_amount ?? 0);
            $row['pmt_sold_L30'] = (int) ($matchedGeneralL30->sales ?? 0);
            $row['pmt_clicks_L30'] = (int) ($matchedGeneralL30->clicks ?? 0);
            $row['SPEND_L30'] = $row['kw_spend_L30'] + $row['pmt_spend_L30'];
            $row['SALES_L30'] = $row['kw_sales_L30'] + $row['pmt_sales_L30'];
            $row['SOLD_L30'] = $row['kw_sold_L30'] + $row['pmt_sold_L30'];
            $row['CLICKS_L30'] = $row['kw_clicks_L30'] + $row['pmt_clicks_L30'];
    
            if($row['campaignName'] !== ''){
                $result[] = $row;
            }
        }

        $totals = [
            'ebay_SALES_L30_total' => 0,
            'ebay_kw_sales_L30_total' => 0,
            'ebay_pmt_sales_L30_total' => 0,
            'ebay_SPEND_L30_total' => 0,
            'ebay_kw_spend_L30_total' => 0,
            'ebay_pmt_spend_L30_total' => 0,
            'ebay_CLICKS_L30_total' => 0,
            'ebay_kw_clicks_L30_total' => 0,
            'ebay_pmt_clicks_L30_total' => 0,
            'ebay_SOLD_L30_total' => 0,
            'ebay_kw_sold_L30_total' => 0,
            'ebay_pmt_sold_L30_total' => 0,
        ];

        $map = [
            'SALES_L30' => 'ebay_SALES_L30_total',
            'kw_sales_L30' => 'ebay_kw_sales_L30_total',
            'pmt_sales_L30' => 'ebay_pmt_sales_L30_total',
            'SPEND_L30' => 'ebay_SPEND_L30_total',
            'kw_spend_L30' => 'ebay_kw_spend_L30_total',
            'pmt_spend_L30' => 'ebay_pmt_spend_L30_total',
            'CLICKS_L30' => 'ebay_CLICKS_L30_total',
            'kw_clicks_L30' => 'ebay_kw_clicks_L30_total',
            'pmt_clicks_L30' => 'ebay_pmt_clicks_L30_total',
            'SOLD_L30' => 'ebay_SOLD_L30_total',
            'kw_sold_L30' => 'ebay_kw_sold_L30_total',
            'pmt_sold_L30' => 'ebay_pmt_sold_L30_total',
        ];

        foreach ($result as $row) {
            $sku = strtolower(trim($row['sku'] ?? ''));
            if (strpos($sku, 'parent ') !== false) continue;

            foreach ($map as $key => $totalKey) {
                $totals[$totalKey] += (float)($row[$key] ?? 0);
            }
        }
        extract($totals);
       

        /** End Ebay Code  */


        /** Start Amazon Missing data **/

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();

        $amazonDatasheetsBySku = AmazonDatasheet::whereIn('sku', $skus)->get()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy('sku');

        $nrValues = AmazonDataView::whereIn('sku', $skus)->pluck('value', 'sku');

        $amazonKwCampaigns = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) $q->orWhere('campaignName', 'LIKE', '%' . $sku . '%');
            })
            ->where('campaignName', 'NOT LIKE', '%PT')
            ->where('campaignName', 'NOT LIKE', '%PT.')
            ->where('campaignStatus', '!=', 'ARCHIVED')
            ->get();

        $amazonPtCampaigns = AmazonSpCampaignReport::where('ad_type', 'SPONSORED_PRODUCTS')
            ->where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaignName', 'LIKE', '%' . strtoupper($sku) . '%');
                }
            })
            ->where('campaignStatus', '!=', 'ARCHIVED')
            ->get();

        $result = [];

        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            $parent = $pm->parent;

            $amazonSheet = $amazonDatasheetsBySku[$sku] ?? null;
            $shopify = $shopifyData[$pm->sku] ?? null;

            $matchedKwCampaign = $amazonKwCampaigns->first(function ($item) use ($sku) {
                $campaignName = strtoupper(trim(rtrim($item->campaignName, '.')));
                $cleanSku = strtoupper(trim(rtrim($sku, '.')));
                return $campaignName === $cleanSku;
            });

            $matchedPtCampaign = $amazonPtCampaigns->first(function ($item) use ($sku) {
                $cleanName = strtoupper(trim($item->campaignName));

                return (
                    (str_ends_with($cleanName, $sku . ' PT') || str_ends_with($cleanName, $sku . ' PT.'))
                );
            });

            $row = [
                'parent' => $parent,
                'sku' => $pm->sku,
                'INV' => $shopify->inv ?? 0,
                'L30' => $shopify->quantity ?? 0,
                'A_L30' => $amazonSheet->units_ordered_l30 ?? 0,
                'kw_campaign_name' => $matchedKwCampaign->campaignName ?? '',
                'pt_campaign_name' => $matchedPtCampaign->campaignName ?? '',
                'campaignStatus' => $matchedKwCampaign->campaignStatus ?? '',
                'NRL' => '',
                'NRA' => '',
                'FBA' => '',
            ];

            if (isset($nrValues[$pm->sku])) {
                $raw = $nrValues[$pm->sku];
                if (!is_array($raw)) {
                    $raw = json_decode($raw, true);
                }
                if (is_array($raw)) {
                    $row['NRL']  = $raw['NRL'] ?? null;
                    $row['NRA'] = $raw['NRA'] ?? null;
                    $row['FBA'] = $raw['FBA'] ?? null;
                    $row['TPFT'] = $raw['TPFT'] ?? null;
                }
            }
            
            $result[] = $row;
        }

        $filters = [
            'global_search' => 'shoes',
            'status' => 'Active',
            'inv' => 'INV_0',
            'nra' => 'RA',
            'missingAds' => 'KW Missing'
        ];

        $visibleData = $this->combinedFilter($result, $filters);

        $bothRunning = 0;
        $ptMissing = 0;
        $kwMissing = 0;
        $bothMissing = 0;
        $totalMissingAds = 0;

        foreach ($visibleData as $row) {
            $kw = isset($row['kw_campaign_name']) ? $row['kw_campaign_name'] : '';
            $pt = isset($row['pt_campaign_name']) ? $row['pt_campaign_name'] : '';
            $nra = isset($row['NRA']) ? trim($row['NRA']) : '';

            if ($nra !== 'NRA') {
                if (!empty($kw) && !empty($pt)) {
                    $bothRunning++;
                } elseif (!empty($kw) && empty($pt)) {
                    $ptMissing++;
                } elseif (empty($kw) && !empty($pt)) {
                    $kwMissing++;
                } else {
                    $bothMissing++;
                }
            }

            if ($nra !== 'NRA') {
                $totalMissingAds = $ptMissing + $kwMissing + $bothMissing;
            }
        }
        $ptMissing = $ptMissing + $bothMissing;
        $kwMissing = $kwMissing + $bothMissing;

        /** End Amazon Missing Data **/

        /** Start ebay Missing data **/
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
                'pmt_bid_percentage' => ($ebayMetric && isset($ebayMetric->item_id) && isset($campaignListings[$ebayMetric->item_id])) ? $campaignListings[$ebayMetric->item_id]->bid_percentage : null,
                'campaignStatus' => $campaignReport->campaignStatus ?? null,
            ];
        }

        $visibleData = $this->combinedFilter($result, $filters);

        $ebaybothRunning = $ebayptMissing = $ebaykwMissing = $ebaybothMissing = 0;
        foreach ($visibleData as $row) {
            $nra = trim($row['NRA'] ?? '');
            if ($nra === 'NRA') continue; // ✅ skip early

            $kw = $row['kw_campaign_name'] ?? '';
            $pt = $row['pmt_bid_percentage'] ?? '';

            if ($kw && $pt) {
                $ebaybothRunning++;
            } elseif ($kw && !$pt) {
                $ebayptMissing++;
            } elseif (!$kw && $pt) {
                $ebaykwMissing++;
            } else {
                $ebaybothMissing++;
            }
        }
        $ebaytotalMissingAds = $ebayptMissing + $ebaykwMissing + $ebaybothMissing;
        $ebaykwMissing += $ebaybothMissing;
        $ebayptMissing += $ebaybothMissing;
        
        /** End ebay Missing Data **/

        /** Start Total Sales Data */

        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

        $skus = $productMasters->pluck('sku')->filter()->unique()->values()->all();
        $amazonDatasheetsBySku = AmazonDatasheet::whereIn('sku', $skus)->get()->keyBy(function ($item) {
            return strtoupper($item->sku);
        });

        $result = [];
        $totalSales = 0;
        foreach ($productMasters as $pm) {
            $sku = strtoupper($pm->sku);
            if (str_starts_with($sku, 'PARENT ')) {
                continue;
            }
            $amazonSheet = $amazonDatasheetsBySku[$sku] ?? null;
            $row = [];
            if ($amazonSheet) {
                $row['A_L30'] = $amazonSheet->units_ordered_l30;
                $row['price'] = $amazonSheet->price;
            }

            $price = isset($row['price']) ? floatval($row['price']) : 0;
            $units_ordered_l30 = isset($row['A_L30']) ? floatval($row['A_L30']) : 0;

            $row['T_Sale_l30'] = round($price * $units_ordered_l30, 2);
            $T_Sale_l30 = round($price * $units_ordered_l30, 2);
            $totalSales = $totalSales + $T_Sale_l30; 
        }
        
        /** End Total Sales Data ***/
            $productMasters = ProductMaster::orderBy("parent", "asc")
                ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
                ->orderBy("sku", "asc")
                ->get();
            $skus = $productMasters->pluck("sku")
                ->filter()
                ->unique()
                ->values()
                ->all();
            $ebayMetrics = DB::connection('apicentral')->table('ebay_one_metrics')->whereIn('sku', $skus)->get()->keyBy('sku');
            // 6. Build Result
            $result = [];
            $totalEbaySales = 0;
            foreach ($productMasters as $pm) {
                $sku = strtoupper($pm->sku);
                $ebayMetric = $ebayMetrics[$pm->sku] ?? null;
                $row = [];

                $row["eBay L30"] = $ebayMetric->ebay_l30 ?? 0;
                $row["eBay Price"] = $ebayMetric->ebay_price ?? 0;

                $ebaySales = $row["eBay L30"] * $row["eBay Price"];
                $totalEbaySales = $totalEbaySales + $ebaySales;
            }
        /** End Total Sales for Ebay **/

        /** Start Walmart Data */    
        $normalizeSku = fn($sku) => strtoupper(trim($sku));
        $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();
        $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();
        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $walmartProductSheet = WalmartProductSheet::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $nrValues = WalmartDataView::whereIn('sku', $skus)->pluck('value', 'sku');
        $walmartCampaignReportsAll = WalmartCampaignReport::whereIn('campaignName', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->campaignName));
        $walmartCampaignReportsL30 = WalmartCampaignReport::where('report_range', 'L30')->whereIn('campaignName', $skus)->get();
        $walmartCampaignReportsL7  = WalmartCampaignReport::where('report_range', 'L7')->whereIn('campaignName', $skus)->get();
        $result = [];
        foreach ($productMasters as $pm) {
            $sku = $normalizeSku($pm->sku);
            $parent = $pm->parent;
            $amazonSheet = $walmartProductSheet[$sku] ?? null;
            $shopify = $shopifyData[$sku] ?? null;
            // Campaign name & budget without report_range
            $matchedCampaign = $walmartCampaignReportsAll[$sku] ?? null;
            if (!$matchedCampaign) {
                continue;
            }
            // Metrics by report_range
            $matchedCampaignL30 = $walmartCampaignReportsL30->first(fn($item) => $normalizeSku($item->campaignName) === $sku);
            $matchedCampaignL7  = $walmartCampaignReportsL7->first(fn($item) => $normalizeSku($item->campaignName) === $sku);
            $row = [];
            $row['parent'] = $parent;
            $row['sku']    = $pm->sku;
            $row['INV']    = $shopify->inv ?? 0;
            $row['L30']    = $shopify->quantity ?? 0;
            $row['WA_L30'] = $amazonSheet->l30 ?? 0;

            $row['campaignName'] = $matchedCampaign->campaignName ?? '';
            $row['campaignBudgetAmount'] = $matchedCampaign->budget ?? '';
            $row['campaignStatus'] = $matchedCampaign->status ?? '';

            //kw
            $row['kw_spend_L30'] = $matchedCampaignL30->spend ?? 0;
            $row['kw_spend_L7'] = $matchedCampaignL7->spend ?? 0;
            $row['kw_sales_L30'] = $matchedCampaignL30->sales ?? 0;
            $row['kw_sales_L7'] = $matchedCampaignL7->sales ?? 0;
            $row['kw_sold_L30'] = (int) ($matchedCampaignL30->sold ?? 0);
            $row['kw_sold_L7'] = (int) ($matchedCampaignL7->sold ?? 0);
            $row['kw_clicks_L30'] = (int) ($matchedCampaignL30?->clicks ?? 0);
            $row['kw_clicks_L7'] = (int) ($matchedCampaignL7?->clicks ?? 0);
            $row['kw_impr_L30'] = (int) ($matchedCampaignL30?->impression ?? 0);
            $row['kw_impr_L7'] = (int) ($matchedCampaignL7?->impression ?? 0);

            $row['SPEND_L30'] = $row['kw_spend_L30'];
            $row['SPEND_L7'] = $row['kw_spend_L7'];
            $row['SALES_L30'] = $row['kw_sales_L30'];
            $row['SALES_L7'] = $row['kw_sales_L7'];
            $row['SOLD_L30'] = $row['kw_sold_L30'];
            $row['SOLD_L7'] = $row['kw_sold_L7'];
            $row['CLICKS_L30'] = $row['kw_clicks_L30'];
            $row['CLICKS_L7'] = $row['kw_clicks_L7'];
            $row['IMP_L30'] = $row['kw_impr_L30'];
            $row['IMP_L7'] = $row['kw_impr_L7'];

            $row['NRA'] = '';
            if (isset($nrValues[$pm->sku])) {
                $raw = $nrValues[$pm->sku];
                if (!is_array($raw)) {
                    $raw = json_decode($raw, true);
                }
                if (is_array($raw)) {
                    $row['NRA'] = $raw['NRA'] ?? '';
                }
            }
            if($row['campaignName'] !== ''){
                $result[] = $row;
            }
        }

        $walmart_SALES_L30_Total = 0;
        $walmart_SPEND_L30_Total = 0;
        $walmart_CLICKS_L30_Total = 0;
        $walmart_SOLD_L30_Total = 0;
        foreach ($result as $row) 
        {
            //$sku = strtolower(trim($row['sku'] ?? ''));
            $walmart_SALES_L30_value = $row['SALES_L30'] ?? 0;
            $walmart_SALES_L30_Total += (float)$walmart_SALES_L30_value;

            $walmart_SPEND_L30_value = $row['SPEND_L30'] ?? 0;
            $walmart_SPEND_L30_Total += (float)$walmart_SPEND_L30_value;

            $walmart_CLICKS_L30_Value =  $row['CLICKS_L30'] ?? 0;
            $walmart_CLICKS_L30_Total += (float)$walmart_CLICKS_L30_Value;

            $walmart_SOLD_L30_value = $row['SOLD_L30'] ?? 0;
            $walmart_SOLD_L30_Total += (float)$walmart_SOLD_L30_value;
        }

        /** End Walmart data **/

        /** Start Code for Ebay-3 Add Running List */

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


        $ebay3_SPEND_L30_Total = 0;  
        $ebay3_kw_spend_L30_total = 0;
        $ebay3_pmt_spend_L30_Total = 0;
        $ebay3_CLICKS_L30_Total = 0; 
        $ebay3_kw_clicks_L30_Total = 0;
        $ebay3_pmt_clicks_L30_Total = 0;
        $ebay3_SALES_L30_Total = 0;
        $ebay3_kw_sales_L30_Total = 0;
        $ebay3_pmt_sales_L30_Total = 0;
        $ebay3_SOLD_L30_Total = 0;
        $ebay3_kw_sold_L30_Total = 0;
        $ebay3_pmt_sold_L30_Total = 0;

        foreach ($result as $row) 
        {
            $sku = strtolower($row['sku'] ?? '');

            $ebay3_SPEND_L30_Value = $row['SPEND_L30'] ?? 0;
            if (is_string($ebay3_SPEND_L30_Value)) {
                $ebay3_SPEND_L30_Value = floatval($ebay3_SPEND_L30_Value);
            }
            $ebay3_SPEND_L30_Total += $ebay3_SPEND_L30_Value ?: 0;


            $ebay3_kw_spend_L30_value = $row['kw_spend_L30'] ?? 0;
            if (is_string($ebay3_kw_spend_L30_value)) {
                $ebay3_kw_spend_L30_value = floatval($ebay3_kw_spend_L30_value);
            }
            $ebay3_kw_spend_L30_total += $ebay3_kw_spend_L30_value ?: 0;


            $ebay3_pmt_spend_L30_Value = $row['pmt_spend_L30'] ?? 0;
            if (is_string($ebay3_pmt_spend_L30_Value)) {
                $ebay3_pmt_spend_L30_Value = floatval($ebay3_pmt_spend_L30_Value);
            }
            $ebay3_pmt_spend_L30_Total += $ebay3_pmt_spend_L30_Value ?: 0;


            $ebay3_CLICKS_L30_Value = $row['CLICKS_L30'] ?? 0;
            if (is_string($ebay3_CLICKS_L30_Value)) {
                $ebay3_CLICKS_L30_Value = floatval($ebay3_CLICKS_L30_Value);
            }
            $ebay3_CLICKS_L30_Total += $ebay3_CLICKS_L30_Value ?: 0;


            $ebay3_kw_clicks_L30_Value = $row['kw_clicks_L30'] ?? 0;
            if (is_string($ebay3_kw_clicks_L30_Value)) {
                $ebay3_kw_clicks_L30_Value = floatval($ebay3_kw_clicks_L30_Value);
            }
            $ebay3_kw_clicks_L30_Total += $ebay3_kw_clicks_L30_Value ?: 0;


            $ebay3_pmt_clicks_L30_Value = $row['pmt_clicks_L30'] ?? 0;
            if (is_string($ebay3_pmt_clicks_L30_Value)) {
                $ebay3_pmt_clicks_L30_Value = floatval($ebay3_pmt_clicks_L30_Value);
            }
            $ebay3_pmt_clicks_L30_Total += $ebay3_pmt_clicks_L30_Value ?: 0;

            $ebay3_SALES_L30_Value = $row['SALES_L30'] ?? 0;
            if (is_string($ebay3_SALES_L30_Value)) {
                $ebay3_SALES_L30_Value = floatval($ebay3_SALES_L30_Value);
            }
            $ebay3_SALES_L30_Total += $ebay3_SALES_L30_Value ?: 0;

            $ebay3_kw_sales_L30_Value = $row['kw_sales_L30'] ?? 0;
            if (is_string($ebay3_kw_sales_L30_Value)) {
                $ebay3_kw_sales_L30_Value = floatval($ebay3_kw_sales_L30_Value);
            }
            $ebay3_kw_sales_L30_Total += $ebay3_kw_sales_L30_Value ?: 0;


            $ebay3_pmt_sales_L30_Value = $row['pmt_sales_L30'] ?? 0;
            if (is_string($ebay3_pmt_sales_L30_Value)) {
                $ebay3_pmt_sales_L30_Value = floatval($ebay3_pmt_sales_L30_Value);
            }
            $ebay3_pmt_sales_L30_Total += $ebay3_pmt_sales_L30_Value ?: 0;

            $ebay3_SOLD_L30_Value = $row['SOLD_L30'] ?? 0;
            if (is_string($ebay3_SOLD_L30_Value)) {
                $ebay3_SOLD_L30_Value = floatval($ebay3_SOLD_L30_Value);
            }
            $ebay3_SOLD_L30_Total += $ebay3_SOLD_L30_Value ?: 0;


            $ebay3_kw_sold_L30_Value = $row['kw_sold_L30'] ?? 0;
            if (is_string($ebay3_kw_sold_L30_Value)) {
                $ebay3_kw_sold_L30_Value = floatval($ebay3_kw_sold_L30_Value);
            }
            $ebay3_kw_sold_L30_Total += $ebay3_kw_sold_L30_Value ?: 0;
            
            $ebay3_pmt_sold_L30_Value = $row['pmt_sold_L30'] ?? 0;
            if (is_string($ebay3_pmt_sold_L30_Value)) {
                $ebay3_pmt_sold_L30_Value = floatval($ebay3_pmt_sold_L30_Value);
            }
            $ebay3_pmt_sold_L30_Total += $ebay3_pmt_sold_L30_Value ?: 0;

        }

        /** End Code for Ebay-3 Add Running List **/

        /** Start Code for Ebay-2 Running Ads Data */
        
            $normalizeSku = fn($sku) => strtoupper(trim($sku));

            $productMasters = ProductMaster::orderBy('parent', 'asc')
            ->orderByRaw("CASE WHEN sku LIKE 'PARENT %' THEN 1 ELSE 0 END")
            ->orderBy('sku', 'asc')
            ->get();

            $skus = $productMasters->pluck('sku')->filter()->map($normalizeSku)->unique()->values()->all();

            $shopifyData = ShopifySku::whereIn('sku', $skus)->get()->keyBy(fn($item) => $normalizeSku($item->sku));

            $ebayMetricData = DB::connection('apicentral')->table('ebay2_metrics')
                ->select('sku', 'ebay_price', 'item_id')
                ->whereIn('sku', $skus)
                ->get()
                ->keyBy(fn($item) => $normalizeSku($item->sku));

            $nrValues = EbayTwoDataView::whereIn('sku', $skus)->pluck('value', 'sku');

            $itemIds = $ebayMetricData->pluck('item_id')->toArray();
        
            $ebayGeneralReportsL30 = Ebay2GeneralReport::where('report_range', 'L30')
                ->whereIn('listing_id', $itemIds)
                ->get();

            $ebayGeneralReportsL7 = Ebay2GeneralReport::where('report_range', 'L7')
                ->whereIn('listing_id', $itemIds)
                ->get();

            $result = [];

            foreach ($productMasters as $pm) {
                $sku = strtoupper($pm->sku);
                $parent = $pm->parent;

                $shopify = $shopifyData[$sku] ?? null;
                $ebay = $ebayMetricData[$sku] ?? null;
            
                $matchedGeneralL30 = $ebayGeneralReportsL30->first(function ($item) use ($ebay) {
                    if (!$ebay || empty($ebay->item_id)) return false;
                    return trim((string)$item->listing_id) == trim((string)$ebay->item_id);
                });

                $matchedGeneralL7 = $ebayGeneralReportsL7->first(function ($item) use ($ebay) {
                    if (!$ebay || empty($ebay->item_id)) return false;
                    return trim((string)$item->listing_id) == trim((string)$ebay->item_id);
                });

                if (!$matchedGeneralL30 && !$matchedGeneralL7) {
                    continue;
                }

                $row = [];

                $row['parent'] = $parent;
                $row['sku'] = $pm->sku;
                $row['INV'] = $shopify->inv ?? 0;
                $row['L30'] = $shopify->quantity ?? 0;
                $row['e_l30'] = $ebay->ebay_l30 ?? 0;

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

                $row['SPEND_L30'] = $row['pmt_spend_L30'];
                $row['SPEND_L7'] = $row['pmt_spend_L7'];
                $row['SALES_L30'] = $row['pmt_sales_L30'];
                $row['SALES_L7'] = $row['pmt_sales_L7'];
                $row['SOLD_L30'] = $row['pmt_sold_L30'];
                $row['SOLD_L7'] = $row['pmt_sold_L7'];
                $row['CLICKS_L30'] = $row['pmt_clicks_L30'];
                $row['CLICKS_L7'] = $row['pmt_clicks_L7'];
                $row['IMP_L30'] = $row['pmt_impr_L30'];
                $row['IMP_L7'] = $row['pmt_impr_L7'];

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

                $result[] = $row;
            }

            $ebay2_SPEND_L30_Total = 0;
            $ebay2_pmt_spend_L30_Total = 0;
            $ebay2_CLICKS_L30_Total = 0;
            $ebay2_pmt_clicks_L30_Total = 0;
            $ebay2_SALES_L30_Total = 0;
            $ebay2_pmt_sales_L30_total = 0;
            $ebay2_SOLD_L30_Total = 0;
            $ebay2_pmt_sold_L30_Total = 0;

            foreach ($result as $row) {
                $sku = strtolower($row['sku'] ?? '');

                // $value = $row[$field] ?? 0;
                // $total += is_numeric($value) ? (float)$value : 0;

                $ebay2_SPEND_L30_Value = $row['SPEND_L30'] ?? 0;
                $ebay2_SPEND_L30_Total += is_numeric($ebay2_SPEND_L30_Value) ? (float)$ebay2_SPEND_L30_Value : 0;


                $ebay2_pmt_spend_L30_Value = $row['pmt_spend_L30'] ?? 0;
                $ebay2_pmt_spend_L30_Total += is_numeric($ebay2_pmt_spend_L30_Value) ? (float)$ebay2_pmt_spend_L30_Value : 0;

                $ebay2_CLICKS_L30_Value = $row['CLICKS_L30'] ?? 0;
                $ebay2_CLICKS_L30_Total += is_numeric($ebay2_CLICKS_L30_Value) ? (float)$ebay2_CLICKS_L30_Value : 0;

                $ebay2_pmt_clicks_L30_Value = $row['pmt_clicks_L30'] ?? 0;
                $ebay2_pmt_clicks_L30_Total += is_numeric($ebay2_pmt_clicks_L30_Value) ? (float)$ebay2_pmt_clicks_L30_Value : 0;

                $ebay2_SALES_L30_Value = $row['SALES_L30'] ?? 0;
                $ebay2_SALES_L30_Total += is_numeric($ebay2_SALES_L30_Value) ? (float)$ebay2_SALES_L30_Value : 0;

                $ebay2_pmt_sales_L30_value = $row['pmt_sales_L30'] ?? 0;
                $ebay2_pmt_sales_L30_total += is_numeric($ebay2_pmt_sales_L30_value) ? (float)$ebay2_pmt_sales_L30_value : 0;

                $ebay2_SOLD_L30_Value = $row['SOLD_L30'] ?? 0;
                $ebay2_SOLD_L30_Total += is_numeric($ebay2_SOLD_L30_Value) ? (float)$ebay2_SOLD_L30_Value : 0;

                $ebay2_pmt_sold_L30_Value = $row['pmt_sold_L30'] ?? 0;
                $ebay2_pmt_sold_L30_Total += is_numeric($ebay2_pmt_sold_L30_Value) ? (float)$ebay2_pmt_sold_L30_Value : 0;
            }


        /** End Code for Ebay-2 Running Ads Data **/

        /** Start Ebay-2 Missing Data  */
            
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
            $nrValues = EbayTwoDataView::whereIn('sku', $skus)->pluck('value', 'sku');
            $ebayMetricData = DB::connection('apicentral')->table('ebay2_metrics')
                ->select('sku', 'ebay_price', 'item_id')
                ->whereIn('sku', $skus)
                ->get()
                ->keyBy(fn($item) => $normalizeSku($item->sku));

            $campaignListings = DB::connection('apicentral')
                ->table('ebay2_campaign_ads_listings')
                ->select('listing_id', 'bid_percentage')
                ->get()
                ->keyBy('listing_id')
                ->toArray();

            $result = [];

            foreach ($productMasters as $pm) {
                $sku = strtoupper($pm->sku);
                $shopify = $shopifyData->get($sku);
                $ebayMetric = $ebayMetricData->get($sku);
                
                $nrActual = null;
                if (isset($nrValues[$pm->sku])) {
                    $raw = $nrValues[$pm->sku];
                    if (!is_array($raw)) {
                        $raw = json_decode($raw, true);
                    }
                    if (is_array($raw)) {
                        $nrActual = $raw['NRA'] ?? null;
                    }
                }

                $result[] = [
                    'sku' => $sku,
                    'parent' => $pm->parent,
                    'INV' => $shopify->inv ?? 0,
                    'L30' => $shopify->quantity ?? 0,
                    'NRA' => $nrActual,
                    'pmt_bid_percentage' => ($ebayMetric && isset($ebayMetric->item_id) && isset($campaignListings[$ebayMetric->item_id])) 
                        ? $campaignListings[$ebayMetric->item_id]->bid_percentage 
                        : null,
                ];
            }

            $filters = [
                'global_search' => $_GET['global-search'] ?? '',
                'status'        => $_GET['status-filter'] ?? '',
                'inv_filter'    => $_GET['inv-filter'] ?? '',
                'nra_filter'    => $_GET['nra-filter'] ?? '',
                'missing_ads'   => $_GET['missingAds-filter'] ?? ''
            ];

            $filteredRows = array_filter($result, function($row) use ($filters) {
                return $this->Ebay2combinedFilter($row, $filters);
            });

            $ebay2_ptMissing = 0;
            foreach ($filteredRows as $row) {
                $pt = $row['pmt_bid_percentage'] ?? '';
                $nra = trim($row['NRA'] ?? '');

                if ($nra !== 'NRA') {
                    if (empty($pt)) $ebay2_ptMissing++; 
                }
            }

        /** End Ebay-2 Missing Data **/

        /** Start Ebay-3 Missing Data **/
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
            $nrValues = EbayThreeDataView::whereIn('sku', $skus)->pluck('value', 'sku');
            $ebayMetricData = DB::table('ebay_3_metrics')
                ->select('sku', 'ebay_price', 'item_id')
                ->whereIn('sku', $skus)
                ->get()
                ->keyBy(fn($item) => $normalizeSku($item->sku));

            // Fetch campaign reports and create efficient lookup
            $ebayCampaignReports = Ebay3PriorityReport::where(function ($q) use ($skus) {
                foreach ($skus as $sku) {
                    $q->orWhere('campaign_name', 'LIKE', '%' . $sku . '%');
                }
            })->get();

            $campaignLookup = [];
            foreach ($ebayCampaignReports as $campaign) {
                foreach ($skus as $sku) {
                    $normalizedCampaignName = strtoupper(trim($campaign->campaign_name)); 
                    if (stripos($normalizedCampaignName, $sku) !== false) { 
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
                
                $nrActual = null;
                if (isset($nrValues[$pm->sku])) {
                    $raw = $nrValues[$pm->sku];
                    if (!is_array($raw)) {
                        $raw = json_decode($raw, true);
                    }
                    if (is_array($raw)) {
                        $nrActual = $raw['NRA'] ?? null;
                    }
                }

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

            $filters = [
                'global_search' => $_GET['global-search'] ?? '',
                'status_filter' => $_GET['status-filter'] ?? '',
                'inv_filter'    => $_GET['inv-filter'] ?? '',
                'nra_filter'    => $_GET['nra-filter'] ?? '',
                'missing_ads'   => $_GET['missingAds-filter'] ?? ''
            ];

            $filteredRows = array_filter($result, function($row) use ($filters) {
                return $this->Ebay3combinedFilter($row, $filters);
            });

            $ebay3bothMissing = 0;
            $ebay3kwMissing = 0;
            $ebay3ptMissing = 0;
            $ebay3bothRunning = 0;
            $ebay3kwRunning = 0;
            $ebay3ptRunning = 0;
            $ebay3totalMissingAds = 0;
            $ebay3totalNRA = 0;
            $ebay3totalRA = 0;

            foreach ($filteredRows as $row) {
                $kw = $row['kw_campaign_name'] ?? '';
                $pt = $row['pmt_bid_percentage'] ?? '';
                $nra = trim($row['NRA'] ?? '');

                // ✅ Existing counts
                if ($nra !== "NRA") {
                    if ($kw && $pt) $ebay3bothRunning++;
                    elseif ($kw && !$pt) $ebay3ptMissing++;
                    elseif (!$kw && $pt) $ebay3kwMissing++;
                    else $ebay3bothMissing++;
                }

                // ✅ Running counts
                if ($nra !== "NRA") {
                    if ($kw) $ebay3kwRunning++;
                    if ($pt) $ebay3ptRunning++;
                }

                // ✅ Total Missing Ads Count
                if ($nra !== "NRA") {
                    $ebay3totalMissingAds = $ebay3ptMissing + $ebay3kwMissing + $ebay3bothMissing;
                }

                // ✅ NRA and RA counts
                if ($nra === "NRA") $ebay3totalNRA++;
                else $ebay3totalRA++;
            }
            $ebay3kwMissing = $ebay3bothMissing + $ebay3kwMissing; 
            $ebay3ptMissing = $ebay3bothMissing + $ebay3ptMissing;

        /** End Ebay-3 Missing data ***/

        $roundVars = [
            'ebay_SALES_L30_total', 'ebay_kw_sales_L30_total', 'ebay_pmt_sales_L30_total',
            'ebay_SPEND_L30_total', 'ebay_kw_spend_L30_total', 'ebay_pmt_spend_L30_total',
            'ebay_CLICKS_L30_total', 'ebay_kw_clicks_L30_total', 'ebay_pmt_clicks_L30_total',
            'ebay_SOLD_L30_total', 'ebay_kw_sold_L30_total', 'ebay_pmt_sold_L30_total',
            'SPEND_L30_total', 'kw_spend_L30_total', 'pt_spend_L30_total', 'hl_spend_L30_total',
            'CLICKS_L30_total', 'kw_clicks_L30_total', 'pt_clicks_L30_total', 'hl_clicks_L30_total',
            'totalSales', 'totalEbaySales','SOLD_L30_Total', 'kw_sold_L30_Total', 'pt_sold_L30_Total', 'hl_sold_L30_Total',
            'SALES_L30_Total', 'kw_sales_L30_Total', 'pt_sales_L30_Total', 'hl_sales_L30_Total', 'walmart_SALES_L30_Total', 'walmart_SPEND_L30_Total', 'walmart_CLICKS_L30_Total', 'walmart_SOLD_L30_Total', 'ebay3_SPEND_L30_Total', 'ebay3_kw_spend_L30_total', 'ebay3_pmt_spend_L30_Total', 'ebay3_CLICKS_L30_Total', 'ebay3_kw_clicks_L30_Total', 'ebay3_pmt_clicks_L30_Total', 'ebay3_SALES_L30_Total', 'ebay3_kw_sales_L30_Total', 'ebay3_pmt_sales_L30_Total', 'ebay3_SOLD_L30_Total', 'ebay3_kw_sold_L30_Total', 'ebay3_pmt_sold_L30_Total', 'ebay2_SPEND_L30_Total', 'ebay2_pmt_spend_L30_Total', 'ebay2_CLICKS_L30_Total', 'ebay2_pmt_clicks_L30_Total', 'ebay2_SALES_L30_Total', 'ebay2_pmt_sales_L30_total', 'ebay2_SOLD_L30_Total', 'ebay2_pmt_sold_L30_Total', 'ebay2_ptMissing', 'ebay3totalMissingAds', 'ebay3kwMissing', 'ebay3ptMissing'
        ];

        foreach ($roundVars as $varName) {
            if (isset($$varName)) {
                $$varName = round((float) $$varName);
            }
        }

        return view('channels.adv-masters', compact('kw_spend_L30_total', 'pt_spend_L30_total', 'hl_spend_L30_total', 'kw_clicks_L30_total', 'pt_clicks_L30_total', 'hl_clicks_L30_total', 'SPEND_L30_total', 'CLICKS_L30_total', 'ebay_SALES_L30_total', 'ebay_kw_sales_L30_total', 'ebay_pmt_sales_L30_total', 'ebay_SPEND_L30_total', 'ebay_kw_spend_L30_total', 'ebay_pmt_spend_L30_total', 'ebay_CLICKS_L30_total', 'ebay_kw_clicks_L30_total', 'ebay_pmt_clicks_L30_total', 'ebay_SOLD_L30_total', 'ebay_kw_sold_L30_total', 'ebay_pmt_sold_L30_total', 'bothMissing', 'totalMissingAds', 'kwMissing', 'ptMissing', 'ebaytotalMissingAds', 'ebaykwMissing', 'ebayptMissing', 'totalSales', 'totalEbaySales', 'SOLD_L30_Total', 'kw_sold_L30_Total', 'pt_sold_L30_Total', 'hl_sold_L30_Total', 'SALES_L30_Total', 'kw_sales_L30_Total', 'pt_sales_L30_Total', 'hl_sales_L30_Total', 'walmart_SALES_L30_Total', 'walmart_SPEND_L30_Total', 'walmart_CLICKS_L30_Total', 'walmart_SOLD_L30_Total', 'ebay3_SPEND_L30_Total', 'ebay3_kw_spend_L30_total', 'ebay3_pmt_spend_L30_Total', 'ebay3_CLICKS_L30_Total', 'ebay3_kw_clicks_L30_Total', 'ebay3_pmt_clicks_L30_Total', 'ebay3_SALES_L30_Total', 'ebay3_kw_sales_L30_Total', 'ebay3_pmt_sales_L30_Total', 'ebay3_SOLD_L30_Total', 'ebay3_kw_sold_L30_Total', 'ebay3_pmt_sold_L30_Total', 'ebay2_SPEND_L30_Total', 'ebay2_pmt_spend_L30_Total', 'ebay2_CLICKS_L30_Total', 'ebay2_pmt_clicks_L30_Total', 'ebay2_SALES_L30_Total', 'ebay2_pmt_sales_L30_total', 'ebay2_SOLD_L30_Total', 'ebay2_pmt_sold_L30_Total', 'ebay2_ptMissing', 'ebay3totalMissingAds', 'ebay3kwMissing', 'ebay3ptMissing'));
    }

    public function combinedFilter($data, $filters) 
    {
        $filtered = [];
        foreach ($data as $row) {
            $sku = strtoupper($row['sku'] ?? '');
            if (strpos($sku, 'PARENT') !== false) continue; // Exclude parent rows

            $searchVal = null;
            if ($searchVal) {
                $fields = [
                    strtolower($row['sku'] ?? ''),
                    strtolower($row['parent'] ?? ''),
                    strtolower($row['kw_campaign_name'] ?? ''),
                    strtolower($row['pt_campaign_name'] ?? ''),
                ];

                $matchFound = false;
                foreach ($fields as $field) {
                    if (strpos($field, $searchVal) !== false) {
                        $matchFound = true;
                        break;
                    }
                }
                if (!$matchFound) continue;
            }

            $statusVal = null;
            if ($statusVal && ($row['campaignStatus'] ?? '') !== $statusVal) continue;

            $invFilterVal = null;
            $inv = floatval($row['INV'] ?? 0);
            if ($invFilterVal === 'INV_0' && $inv != 0) continue;
            if ($invFilterVal === 'OTHERS' && $inv == 0) continue;

            $nraFilterVal = null;
            $nra = trim($row['NRA'] ?? '');
            if ($nraFilterVal) {
                if ($nraFilterVal === 'RA' && $nra === 'NRA') continue;
                elseif ($nraFilterVal !== 'ALL' && $nra !== $nraFilterVal) continue;
            }

            $missingVal = null;
            $kw = $row['kw_campaign_name'] ?? '';
            $pt = $row['pt_campaign_name'] ?? '';

            if ($missingVal === 'Both Running' && !($kw && $pt)) continue;
            if ($missingVal === 'KW Missing' && !($pt && !$kw)) continue;
            if ($missingVal === 'PT Missing' && !($kw && !$pt)) continue;
            if ($missingVal === 'Both Missing' && ($kw || $pt)) continue;

            $filtered[] = $row;
        }
        return $filtered;
    }

    public function combinedFilterebay($data, $filters) 
    {
        $filtered = [];
        foreach ($data as $row) {
            
            $sku = strtoupper($row['sku'] ?? '');
            if (strpos($sku, 'PARENT') !== false) continue;

            $searchVal = null;
            if ($searchVal) {
                $fields = [
                    strtolower($row['sku'] ?? ''),
                    strtolower($row['parent'] ?? ''),
                    strtolower($row['kw_campaign_name'] ?? ''),
                    strtolower($row['pmt_bid_percentage'] ?? ''),
                ];
                $found = false;
                foreach ($fields as $f) {
                    if (strpos($f, $searchVal) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) continue;
            }

            $statusVal = null;
            if ($statusVal && ($row['campaignStatus'] ?? '') !== $statusVal) continue;

            $invFilterVal = null;
            $inv = floatval($row['INV'] ?? 0);
            if ($invFilterVal === 'INV_0' && $inv != 0) continue;
            if ($invFilterVal === 'OTHERS' && $inv == 0) continue;

            $nraFilterVal = null;
            $nra = trim($row['NRA'] ?? '');
            if ($nraFilterVal) {
                if ($nraFilterVal === 'RA' && $nra === 'NRA') continue;
                elseif ($nraFilterVal !== 'ALL' && $nra !== $nraFilterVal) continue;
            }

            $missingVal = null;
            $kw = $row['kw_campaign_name'] ?? '';
            $pt = $row['pmt_bid_percentage'] ?? '';

            if ($missingVal === 'Both Running' && !($kw && $pt)) continue;
            if ($missingVal === 'KW Missing' && !($pt && !$kw)) continue;
            if ($missingVal === 'PMT Missing' && !($kw && !$pt)) continue;
            if ($missingVal === 'Both Missing' && ($kw || $pt)) continue;

            $filtered[] = $row;
        }
        return $filtered;
    }
    public function Ebay2combinedFilter($data, $filters) {
        // 🟢 1. Exclude Parent SKUs
        $sku = $data['sku'] ?? '';
        if (stripos($sku, 'PARENT') !== false) {
            return false;
        }

        // 🟢 2. Global Search
        $searchVal = strtolower(trim($filters['global_search'] ?? ''));
        if ($searchVal) {
            $fields = [
                strtolower($data['sku'] ?? ''),
                strtolower($data['parent'] ?? ''),
                strtolower($data['pmt_bid_percentage'] ?? '')
            ];
            $found = false;
            foreach ($fields as $field) {
                if (strpos($field, $searchVal) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) return false;
        }

        // 🟢 3. Status Filter
        $statusVal = $filters['status'] ?? '';
        if ($statusVal && ($data['campaignStatus'] ?? '') !== $statusVal) {
            return false;
        }

        // 🟢 4. INV Filter
        $invFilter = $filters['inv_filter'] ?? '';
        $inv = floatval($data['INV'] ?? 0);
        if ($invFilter === 'INV_0' && $inv != 0) return false;
        if ($invFilter === 'OTHERS' && $inv == 0) return false;

        // 🟢 5. NRA Filter
        $nraFilter = $filters['nra_filter'] ?? '';
        $nra = trim($data['NRA'] ?? '');
        if ($nraFilter) {
            if ($nraFilter === 'RA' && $nra === 'NRA') return false;
            if ($nraFilter !== 'ALL' && $nra !== $nraFilter) return false;
        }

        // 🟢 6. Missing Ads Filter
        $missingVal = $filters['missing_ads'] ?? '';
        $pt = $data['pmt_bid_percentage'] ?? '';
        if ($missingVal) {
            if ($missingVal === 'PMT Running' && empty($pt)) return false;
            if ($missingVal === 'PMT Missing' && !empty($pt)) return false;
        }

        return true; // ✅ keep this record
    }

    public function Ebay3combinedFilter($data, $filters) {
        // 🟢 1. Exclude parent SKUs
        $sku = $data['sku'] ?? '';
        if (stripos($sku, 'PARENT') !== false) {
            return false;
        }

        // 🟢 2. Global Search
        $searchVal = strtolower(trim($filters['global_search'] ?? ''));
        if ($searchVal) {
            $fields = [
                strtolower($data['sku'] ?? ''),
                strtolower($data['parent'] ?? ''),
                strtolower($data['kw_campaign_name'] ?? ''),
                strtolower($data['pmt_bid_percentage'] ?? '')
            ];

            $found = false;
            foreach ($fields as $field) {
                if (strpos($field, $searchVal) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) return false;
        }

        // 🟢 3. Status Filter
        $statusVal = $filters['status_filter'] ?? '';
        if ($statusVal && ($data['campaignStatus'] ?? '') !== $statusVal) {
            return false;
        }

        // 🟢 4. INV Filter
        $invFilter = $filters['inv_filter'] ?? '';
        $inv = floatval($data['INV'] ?? 0);
        if ($invFilter === 'INV_0' && $inv != 0) return false;
        if ($invFilter === 'OTHERS' && $inv == 0) return false;

        // 🟢 5. NRA Filter
        $nraFilter = $filters['nra_filter'] ?? '';
        $nra = trim($data['NRA'] ?? '');
        if ($nraFilter) {
            if ($nraFilter === 'RA' && $nra === 'NRA') return false;
            elseif ($nraFilter !== 'ALL' && $nra !== $nraFilter) return false;
        }

        // 🟢 6. Missing Ads Filter
        $missingVal = $filters['missing_ads'] ?? '';
        $kw = $data['kw_campaign_name'] ?? '';
        $pt = $data['pmt_bid_percentage'] ?? '';

        if ($missingVal === 'Both Running' && (!($kw && $pt))) return false;
        if ($missingVal === 'KW Missing' && !($pt && !$kw)) return false;
        if ($missingVal === 'PMT Missing' && !($kw && !$pt)) return false;
        if ($missingVal === 'Both Missing' && ($kw || $pt)) return false;

        // ✅ If all checks pass, include this row
        return true;
    }

}
