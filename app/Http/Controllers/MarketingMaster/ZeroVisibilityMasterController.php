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
use Illuminate\Http\Request;



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

        return view('marketing-masters.zero-visibility-master', compact('totalSkuCount', 'zeroInvCount','channels'));
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
            $counts = $record->counts ?? [];
            
            // Get yesterday's count
            $yesterdayCount = $counts[$yesterday] ?? 0;
            $todayCount = $count ?? 0;
            
            // Calculate difference (today - yesterday)
            $difference = $todayCount - $yesterdayCount;
            $todayUpdates += $difference;
            
            // Store update status for each channel
            $channelUpdateData[$channel] = [
                'updated' => $difference != 0,
                'diff' => $difference
            ];
            
            $counts[$today] = $todayCount;
            $record->counts = $counts;
            $record->save();
        }

        $data = array_map(function($channelName) use ($livePendingData, $channelUpdateData) {
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

    public function store(Request $request)
    {
        $data = $request->all();

        // Store or update based on channel name
        $record = ZeroVisibilityMaster::updateOrCreate(
            ['channel_name' => $data['channel_name']],
            [
                'sheet_link' => $data['sheet_link'] ?? null,
                'is_ra_checked' => $data['is_ra_checked'] ?? false,
                'total_sku' => $data['total_sku'] ?? 0,
                'nr' => $data['nr'] ?? 0,
                'listed_req' => $data['listed_req'] ?? 0,
                'listed' => $data['listed'] ?? 0,
                'listing_pending' => $data['listing_pending'] ?? 0,
                'zero_inv' => $data['zero_inv'] ?? 0,
                'live_req' => $data['live_req'] ?? 0,
                'active_and_live' => $data['active_and_live'] ?? 0,
                'live_pending' => $data['live_pending'] ?? 0,
                'zero_visibility_sku_count' => $data['zero_visibility_sku_count'] ?? 0,
                'reason' => $data['reason'] ?? '',
                'step_taken' => $data['step_taken'] ?? '',
            ]
        );

        return response()->json(['message' => 'Saved successfully']);

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
             if ($key === 'amazonfba') {
                $controllerClass = "App\\Http\\Controllers\\FbaDataController";
            } else if (isset($controllerMap[$key])) {
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

                if ($key === 'amazonfba' && method_exists($controller, 'getFbaListedLiveAndViewsData')) {
                    $counts = $controller->getFbaListedLiveAndViewsData();
                    $livePending = $counts['live_pending'] ?? 0;
                    $zeroViews = $counts['zero_view'] ?? 0;
                }
                
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
            $counts = array_filter($counts, function($date) use ($startDate, $endDate) {
                return $date >= $startDate && $date <= $endDate;
            }, ARRAY_FILTER_USE_KEY);
        } else {
            // Default: Show last 7 days
            $today = now()->toDateString();
            $sevenDaysAgo = now()->subDays(6)->toDateString();
            
            $counts = array_filter($counts, function($date) use ($sevenDaysAgo, $today) {
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
