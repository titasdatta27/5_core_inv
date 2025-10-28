<?php

namespace App\Http\Controllers\MarketingMaster;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

use App\Models\MarketplacePercentage;
use App\Models\ZeroVisibilityMaster;
use App\Models\ProductMaster;
use App\Models\SheinDataView;
use App\Models\ShopifySku;
use App\Models\ChannelDailyCount;
use App\Models\SkuAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



class ZeroVisibilityMasterController extends Controller
{

    protected $apiController;

    public function __construct(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productSKUs = ProductMaster::where('sku', 'NOT LIKE', '%PARENT%')
            ->pluck('sku')
            ->toArray();

        $zeroInvCount = ShopifySku::whereIn('sku', $productSKUs)
            ->where('inv', '<=', 0)
            ->count();


        $totalSkuCount = count($productSKUs);

        $channels = ZeroVisibilityMaster::all();

        // Mapping for special channel/controller names
        $controllerMap = [
            'ebay'          => 'EbayZeroController',
            'ebaythree'     => 'Ebay3ZeroController',
            'ebay3'         => 'Ebay3ZeroController',
            'ebaytwo'       => 'Ebay2ZeroController',
            'ebay2'         => 'Ebay2ZeroController',
            'ebayvariation' => 'EbayVariationZeroController',
            'tiktokshop'    => 'TiktokShopZeroController',
            'doba'          => 'DobaZeroController',
            'walmart'       => 'WalmartZeroController',
            'shein'         => 'SheinZeroController',
            'bestbuyusa'    => 'BestbuyUSAZeroController',
            'aliexpress'    => 'AliexpressZeroController',
            'tiendamia'     => 'TiendamiaZeroController',
            'pls'           => 'PLSZeroController',
            'mercariwship'  => 'MercariWShipZeroController',
            'mercariwoship' => 'MercariWoShipZeroController',
            'instagramshop' => 'InstagramShopZeroController',
            'fbshop'        => 'FBShopZeroController',
            'fbmarketplace' => 'FBMarketplaceZeroController',
            'faire'         => 'FaireZeroController',
            'business5core' => 'Business5CoreZeroController',

        ];

        foreach ($channels as $channel) {
            $livePending = null;
            $zeroView = null;

            // Check if channel has special mapping
            $key = strtolower(str_replace([' ', '&', '-', '/'], '', trim($channel->channel_name)));
            if (isset($controllerMap[$key])) {
                $controllerName = $controllerMap[$key];
                if ($controllerName === 'EbayZeroController') {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\{$controllerName}";
                } else {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\ZeroViewMarketPlace\\{$controllerName}";
                }
            } else {
                // Build controller class name dynamically (e.g., "Amazon" => AmazonZeroController)
                $baseName = str_replace([' ', '&', '-', '/'], '', ucwords(strtolower(trim($channel->channel_name))));
                $controllerClass = "App\\Http\\Controllers\\MarketPlace\\{$baseName}ZeroController";
            }

            if (class_exists($controllerClass)) {
                $controller = app($controllerClass);
                if (method_exists($controller, 'getLivePendingAndZeroViewCounts')) {
                    $counts = $controller->getLivePendingAndZeroViewCounts();
                    $livePending = $counts['live_pending'] ?? null;
                    $zeroView = $counts['zero_view'] ?? null;
                }
            }

            $channel->live_pending = $livePending;
            $channel->zero_view = $zeroView;
        }

        return view('marketing-masters.zero-visibility-master', compact('totalSkuCount', 'zeroInvCount', 'channels'));
    }



    public function Zeroviewmasters()
    {
        $productSKUs = ProductMaster::where('sku', 'NOT LIKE', '%PARENT%')
            ->pluck('sku')
            ->toArray();

        $zeroInvCount = ShopifySku::whereIn('sku', $productSKUs)
            ->where('inv', '<=', 0)
            ->count();


        $totalSkuCount = count($productSKUs);

        $channels = MarketplacePercentage::pluck('marketplace')->toArray();

        // Mapping for special channel/controller names
        $controllerMap = [
            'ebay'          => 'EbayZeroController',
            'ebaythree'     => 'Ebay3ZeroController',
            'ebay3'         => 'Ebay3ZeroController',
            'ebaytwo'       => 'Ebay2ZeroController',
            'ebay2'         => 'Ebay2ZeroController',
            'ebayvariation' => 'EbayVariationZeroController',
            'tiktokshop'    => 'TiktokShopZeroController',
            'doba'          => 'DobaZeroController',
            'walmart'       => 'WalmartZeroController',
            'shein'         => 'SheinZeroController',
            'bestbuyusa'    => 'BestbuyUSAZeroController',
            'aliexpress'    => 'AliexpressZeroController',
            'tiendamia'     => 'TiendamiaZeroController',
            'pls'           => 'PLSZeroController',
            'mercariwship'  => 'MercariWShipZeroController',
            'mercariwoship' => 'MercariWoShipZeroController',
            'instagramshop' => 'InstagramShopZeroController',
            'fbshop'        => 'FBShopZeroController',
            'fbmarketplace' => 'FBMarketplaceZeroController',
            'faire'         => 'FaireZeroController',
            'business5core' => 'Business5CoreZeroController',
            // Add more mappings as needed
        ];

        $livePendingData = [];

        foreach ($channels as $channelName) {
            $livePending = null;
            $zeroView = null;

            // Check if channel has special mapping
            $key = strtolower(str_replace([' ', '&', '-', '/'], '', trim($channelName)));
            if (isset($controllerMap[$key])) {
                $controllerName = $controllerMap[$key];
                if ($controllerName === 'EbayZeroController') {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\{$controllerName}";
                } else {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\ZeroViewMarketPlace\\{$controllerName}";
                }
            } else {
                // Build controller class name dynamically (e.g., "Amazon" => AmazonZeroController)
                $baseName = str_replace([' ', '&', '-', '/'], '', ucwords(strtolower(trim($channelName))));
                $controllerClass = "App\\Http\\Controllers\\MarketPlace\\{$baseName}ZeroController";
            }

            if (class_exists($controllerClass)) {
                $controller = app($controllerClass);
                if (method_exists($controller, 'getLivePendingAndZeroViewCounts')) {
                    $counts = $controller->getLivePendingAndZeroViewCounts();
                    $livePending = $counts['live_pending'] ?? null;
                    $zeroView = $counts['zero_view'] ?? null;
                }
            }

            $livePendingData[$channelName] = $livePending;
        }

        // Save today's counts for each channel
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $todayUpdates = 0;
        $channelUpdateData = [];

        foreach ($livePendingData as $channel => $count) {
            $record = ChannelDailyCount::firstOrNew(['channel_name' => $channel]);
            // Decode counts safely — handle string/int/null
            $countsRaw = $record->counts;

            if (is_string($countsRaw)) {
                $decoded = json_decode($countsRaw, true);
                $counts = is_array($decoded) ? $decoded : [];
            } elseif (is_array($countsRaw)) {
                $counts = $countsRaw;
            } else {
                $counts = [];
            }


            $todayCount = $count ?? 0;

            // Find the most recent previous date (strictly before today) in stored counts
            $previousDate = null;
            $previousValue = null;

            if (!empty($counts)) {
                $dates = array_keys($counts);
                // Keep only dates strictly less than today
                $priorDates = array_filter($dates, function ($d) use ($today) {
                    return $d < $today;
                });

                if (!empty($priorDates)) {
                    // Sort prior dates descending to get the latest available
                    rsort($priorDates);
                    $previousDate = $priorDates[0];
                    $previousValue = $counts[$previousDate] ?? 0;
                }
            }

            if ($previousDate !== null) {
                // Compute diff against the most recent prior value
                $difference = $todayCount - $previousValue;
                $updatedFlag = ($difference != 0);
                $todayUpdates += $difference;
            } else {
                // No prior data available -> don't mark as updated and diff is 0
                $difference = 0;
                $updatedFlag = false;
            }

            // Store update status for each channel
            $channelUpdateData[$channel] = [
                'updated' => $updatedFlag,
                'diff' => $difference
            ];

            // Save today's value (ensures future comparisons)
            $counts[$today] = $todayCount;
            $record->counts = $counts;
            $record->save();
        }

        $data = array_map(function ($channelName) use ($livePendingData, $channelUpdateData) {
            return [
                'Channel ' => $channelName,
                'R&A' => false, // placeholder
                'Live Pending' => $livePendingData[$channelName] ?? 0,
                'Updated Today' => $channelUpdateData[$channelName]['updated'] ?? false,
                'Diff' => $channelUpdateData[$channelName]['diff'] ?? 0,
            ];
        }, $channels);

        return view('marketing-masters.live-pending-masters', compact('data', 'totalSkuCount', 'zeroInvCount', 'todayUpdates'));
    }


    public function update(Request $request, string $id)
    {
        $row = ZeroVisibilityMaster::findOrFail($request->id);
        $row->update($request->except('id'));
        return response()->json(['status' => true]);
    }


    public function getMergedChannelData(Request $request)
    {
        // Get all channels from DB
        $channels = MarketplacePercentage::pluck('marketplace')->toArray();

        $data = [];


        // Mapping for special channel/controller names
        $controllerMap = [
            'ebay'          => 'EbayZeroController',
            'ebaythree'     => 'Ebay3ZeroController',
            'ebaytwo'       => 'Ebay2ZeroController',
            'ebay2'         => 'Ebay2ZeroController',
            'ebayvariation' => 'EbayVariationZeroController',
            'tiktokshop'    => 'TiktokShopZeroController',
            'doba'          => 'DobaZeroController',
            'walmart'       => 'WalmartZeroController',
            'shein'         => 'SheinZeroController',
            'bestbuyusa'    => 'BestbuyUSAZeroController',
            'aliexpress'    => 'AliexpressZeroController',
            'tiendamia'     => 'TiendamiaZeroController',
            'pls'           => 'PLSZeroController',
            'mercariwship'  => 'MercariWShipZeroController',
            'mercariwoship' => 'MercariWoShipZeroController',
            'instagramshop' => 'InstagramShopZeroController',
            'fbshop'        => 'FBShopZeroController',
            'fbmarketplace' => 'FBMarketplaceZeroController',
            'faire'         => 'FaireZeroController',
            'business5core' => 'Business5CoreZeroController',
            // Add more mappings as needed
        ];

        foreach ($channels as $channel) {
            $livePending = 0;
            $zeroViews = 0;

            $key = strtolower(str_replace([' ', '-', '&', '/'], '', trim($channel)));
            if (isset($controllerMap[$key])) {
                $controllerName = $controllerMap[$key];
                if ($controllerName === 'EbayZeroController') {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\{$controllerName}";
                } else {
                    $controllerClass = "App\\Http\\Controllers\\MarketPlace\\ZeroViewMarketPlace\\{$controllerName}";
                }
            } else {
                $controllerClass = "App\\Http\\Controllers\\MarketPlace\\" . ucfirst($channel) . "ZeroController";
            }

            if (class_exists($controllerClass)) {
                $controller = app($controllerClass);
                // Try getLivePendingAndZeroViewCounts (preferred, returns both counts)
                if (method_exists($controller, 'getLivePendingAndZeroViewCounts')) {
                    $counts = $controller->getLivePendingAndZeroViewCounts();
                    $livePending = $counts['live_pending'] ?? 0;
                    $zeroViews = $counts['zero_view'] ?? 0;
                }
                // Fallback: try getZeroViewCount (returns only zero view count)
                else if (method_exists($controller, 'getZeroViewCount')) {
                    $zeroViews = $controller->getZeroViewCount();
                }
                // Fallback: try getZeroViewCounts (plural, some controllers use this)
                else if (method_exists($controller, 'getZeroViewCounts')) {
                    $zeroViews = $controller->getZeroViewCounts();
                }
            }

            $data[] = [
                'Channel ' => $channel,          // keep space to match your DataTable
                'R&A' => false,                  // placeholder
                'Live Pending' => $livePending,
                'Zero Visibility SKU Count' => $zeroViews,
            ];
        }

        return response()->json(['data' => $data]);
    }


    public function getChannelChartData(Request $request)
    {
        $channelName = $request->input('channel');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $record = ChannelDailyCount::where('channel_name', $channelName)->first();

        if (!$record || !$record->counts) {
            return response()->json(['dates' => [], 'counts' => []]);
        }

        $counts = $record->counts;
        ksort($counts); // Sort by date

        // Filter by date range
        if ($startDate && $endDate) {
            $counts = array_filter($counts, function ($date) use ($startDate, $endDate) {
                return $date >= $startDate && $date <= $endDate;
            }, ARRAY_FILTER_USE_KEY);
        } else {
            // Default: Show last 7 days
            $today = now()->toDateString();
            $sevenDaysAgo = now()->subDays(6)->toDateString();

            $counts = array_filter($counts, function ($date) use ($sevenDaysAgo, $today) {
                return $date >= $sevenDaysAgo && $date <= $today;
            }, ARRAY_FILTER_USE_KEY);
        }

        $dates = array_keys($counts);
        $values = array_values($counts);

        return response()->json([
            'dates' => $dates,
            'counts' => $values
        ]);
    }
}
