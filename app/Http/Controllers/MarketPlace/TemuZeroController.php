<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use App\Models\TemuDataView;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\MarketplacePercentage;
use App\Models\TemuListingStatus;
use App\Models\TemuMetric;
use Illuminate\Support\Facades\Cache;

class TemuZeroController extends Controller
{
    protected $apiController;

    public function __construct(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function temuZeroView(Request $request)
    {
        $mode = $request->query('mode');
        $demo = $request->query('demo');

        // Get percentage from cache or database
        $percentage = Cache::remember('temu_marketplace_percentage', now()->addDays(30), function () {
            $marketplaceData = MarketplacePercentage::where('marketplace', 'Temu')->first();
            return $marketplaceData ? $marketplaceData->percentage : 100;
        });

        return view('market-places.temuZeroView', [
            'mode' => $mode,
            'demo' => $demo,
            'percentage' => $percentage
        ]);
    }

    /**
     * ✅ Get only SKUs where temu_metric.product_clicks_l30 = 0 and shopify_sku.inv > 0
     */
    public function getViewTemuZeroData(Request $request)
    {
        // Get percentage from cache or database
        $percentage = Cache::remember('temu_marketplace_percentage', now()->addDays(30), function () {
            $marketplaceData = MarketplacePercentage::where('marketplace', 'Temu')->first();
            return $marketplaceData ? $marketplaceData->percentage : 100;
        });
        $percentageValue = $percentage / 100;

        // Fetch all ProductMaster records
        $productMasters = ProductMaster::whereNull('deleted_at')->get();

        // Normalize SKUs
        $skus = $productMasters->pluck('sku')->map(fn($s) => strtoupper(trim($s)))->unique()->toArray();

        // Fetch related data
        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()
            ->keyBy(fn($s) => strtoupper(trim($s->sku)));

        $temuMetrics = TemuMetric::whereIn('sku', $skus)->get()
            ->keyBy(fn($s) => strtoupper(trim($s->sku)));

        $temuDataViews = TemuDataView::whereIn('sku', $skus)->get()
            ->keyBy(fn($s) => strtoupper(trim($s->sku)));

        $processedData = [];
        $slNo = 1;

        foreach ($productMasters as $productMaster) {
            $sku = strtoupper(trim($productMaster->sku));
            $isParent = stripos($sku, 'PARENT') !== false;
            if ($isParent) continue;

            // Get inventory from ShopifySku
            $inv = $shopifyData[$sku]->inv ?? 0;

            // Get product_clicks_l30 from TemuMetric
            $views = null;
            $metric = $temuMetrics[$sku] ?? null;
            if ($metric) {
                $views = $metric->product_clicks_l30 ?? null;
                if ($views === null && !empty($metric->value)) {
                    $metricValue = json_decode($metric->value, true);
                    $views = $metricValue['product_clicks_l30'] ?? null;
                }
            }

            // ✅ Only include SKUs where views = 0 and inv > 0
            if ($inv <= 0 || $views !== 0) {
                continue;
            }

            // Fetch NR and A-Z Reason fields
            $dataView = $temuDataViews[$sku]->value ?? [];
            if (is_string($dataView)) {
                $dataView = json_decode($dataView, true);
            }

            $values = $productMaster->Values ?? [];

            $processedItem = [
                'SL No.' => $slNo++,
                'Sku' => $sku,
                'INV' => $inv,
                'product_clicks_l30' => $views,
                'LP' => $values['lp'] ?? 0,
                'Ship' => $values['ship'] ?? 0,
                'COGS' => $values['cogs'] ?? 0,
                'NR' => $dataView['NR'] ?? 'REQ',
                'A_Z_Reason' => $dataView['A_Z_Reason'] ?? null,
                'A_Z_ActionRequired' => $dataView['A_Z_ActionRequired'] ?? null,
                'A_Z_ActionTaken' => $dataView['A_Z_ActionTaken'] ?? null,
                'percentage' => $percentageValue,
            ];

            $processedData[] = $processedItem;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => array_values($processedData),
            'status' => 200
        ]);
    }

    public function updateReasonAction(Request $request)
    {
        $sku = $request->input('sku');
        $reason = $request->input('reason');
        $actionRequired = $request->input('action_required');
        $actionTaken = $request->input('action_taken');

        if (!$sku) {
            return response()->json([
                'status' => 400,
                'message' => 'SKU is required.'
            ], 400);
        }

        $row = TemuDataView::firstOrCreate(['sku' => $sku]);
        $value = $row->value ?? [];
        $value['A_Z_Reason'] = $reason;
        $value['A_Z_ActionRequired'] = $actionRequired;
        $value['A_Z_ActionTaken'] = $actionTaken;
        $row->value = $value;
        $row->save();

        return response()->json([
            'status' => 200,
            'message' => 'Reason and actions updated successfully.'
        ]);
    }

    /**
     * Optional: Quick summary counts for dashboard
     */
    public function getLivePendingAndZeroViewCounts()
    {
        $productMasters = ProductMaster::whereNull('deleted_at')->get();
        $skus = $productMasters->pluck('sku')->map(fn($s) => strtoupper(trim($s)))->unique()->toArray();

        $shopifyData = ShopifySku::whereIn('sku', $skus)->get()
            ->keyBy(fn($s) => strtoupper(trim($s->sku)));

        $temuMetrics = TemuMetric::whereIn('sku', $skus)->get()
            ->keyBy(fn($s) => strtoupper(trim($s->sku)));

        $zeroViewCount = 0;

        foreach ($productMasters as $item) {
            $sku = strtoupper(trim($item->sku));
            if (stripos($sku, 'PARENT') !== false) continue;

            $inv = floatval($shopifyData[$sku]->inv ?? 0);
            $views = (int)($temuMetrics[$sku]->product_clicks_l30 ?? 0);

            if ($inv > 0 && $views === 0) {
                $zeroViewCount++;
            }
        }

        return [
            'zero_view' => $zeroViewCount,
        ];
    }
}
