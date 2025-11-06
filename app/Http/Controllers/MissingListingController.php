<?php

namespace App\Http\Controllers;


use Illuminate\Support\Arr;

use App\Http\Controllers\Controller;
use App\Models\ProductMaster;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\AutoStockBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// use App\Http\Controllers\ShopifyApiInventoryController;
use App\Models\ShopifySku;
use App\Models\Inventory;
use App\Models\ShopifyInventory;

use App\Models\AmazonDataView;
use App\Models\AmazonListingStatus;
use App\Models\WalmartListingStatus;
use App\Models\ReverbListingStatus;
use App\Models\ProductStockMapping;
use App\Models\SheinListingStatus;
use App\Models\DobaListingStatus;
use App\Models\TemuListingStatus;
use App\Models\MacysListingStatus;
use App\Models\EbayListingStatus;
use App\Models\EbayTwoListingStatus;
use App\Models\EbayThreeListingStatus;
use App\Models\BestbuyUSAListingStatus;
use App\Models\TiendamiaListingStatus;


use App\Services\ShopifyApiService;
use App\Services\AmazonSpApiService;
use App\Services\WalmartApiService;
use App\Services\EbayApiService;
use App\Services\Ebay2ApiService;
use App\Services\Ebay3ApiService;
use App\Services\ReverbApiService;
use App\Services\TemuApiService;
use App\Services\SheinApiService;
use App\Services\DobaApiService;
use App\Services\WayfairApiService;
use App\Services\MacysApiService;
use App\Services\BestBuyApiService;
use App\Services\TiendamiaApiService;
use App\Services\AliExpressApiService;

use GuzzleHttp\Client;

use Aws\Signature\SignatureV4;
use Aws\Credentials\Credentials;




class MissingListingController extends Controller
{

    protected $shopifyDomain;
    protected $shopifyApiKey;
    protected $shopifyPassword;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return view('missing-listing.view-missing-listing');
    }

     
   public function getShopifyMissingInventoryStock(Request $request)
   {    
    
  ini_set('max_execution_time', 300);
     
    // Check if data is older than 1 day
    $latestRecord = ProductStockMapping::orderBy('updated_at', 'desc')->first();    
    // if ($latestRecord && $latestRecord->updated_at > now()->subDay()) {
   if ($latestRecord) {
    // Return cached data from DB
    // $data = ProductStockMapping::all()->keyBy('sku')->unique()->groupby('sku');
    $data = ProductStockMapping::all()
    ->groupBy('sku')
    ->map(function ($items) {
        return $items->first(); // or customize how you want to handle duplicates
    });

$skusforNR = array_values(array_filter(array_map(function ($item) {
    return $item['sku'] ?? null;
}, $data->toArray())));

$marketplaces = [
    'amazon'  => [AmazonListingStatus::class,  'inventory_amazon'],
    'walmart' => [WalmartListingStatus::class, 'inventory_walmart'],
    'reverb'  => [ReverbListingStatus::class,  'inventory_reverb'],
    'shein'   => [SheinListingStatus::class,   'inventory_shein'],
    'doba'    => [DobaListingStatus::class,    'inventory_doba'],
    'temu'    => [TemuListingStatus::class,    'inventory_temu'],
    'macy'    => [MacysListingStatus::class,   'inventory_macy'],
    'ebay1'   => [EbayListingStatus::class,    'inventory_ebay1'],
    'ebay2'   => [EbayTwoListingStatus::class, 'inventory_ebay2'],
    'ebay3'   => [EbayThreeListingStatus::class,'inventory_ebay3'],
    'bestbuy' => [BestbuyUSAListingStatus::class,'inventory_bestbuy'],
    'tiendamia' => [TiendamiaListingStatus::class,'inventory_tiendamia'],
    
];


//  dd($data['FR 10 185 AL 4OHMS']['inventory_macy']);

foreach ($marketplaces as $key => [$model, $inventoryField]) {
    $listingData = $model::whereIn('sku', $skusforNR)->where('value->nr_req', 'NR')->get()->unique()->keyBy('sku');
        
    foreach ($listingData as $sku => $listing) {
          $sku = str_replace("\u{00A0}", ' ', $sku);
            // Trim and normalize spacing
            $sku = trim(preg_replace('/\s+/', ' ', $sku));
            // dd($sku);
        if (
            isset($data[$sku]) &&
            Arr::get($listing->value, 'nr_req') === 'NR'
            && 
            $data[$sku]->$inventoryField>0 
            // && $data[$sku]->$inventoryField!="Not Listed"
        ) {

            $data[$sku]->$inventoryField = 'NRL';
            // if($data[$sku]->$inventoryField != 'Not Listed'){
            // }
        }        
    }
}

// dd($test);

    $datainfo = $this->getDataInfo($data);
    return response()->json([
        'message' => 'Data fetched successfully',
        'data' => $data,
        'datainfo' => $datainfo,
        'status' => 200
    ]);
}

    
    // $freshData=$this->fetchFreshData();   
    // $datainfo=$this->getDataInfo($freshData);

    // return response()->json([
    //     'message' => 'Data fetched successfully',
    //     'data' => $freshData,
    //     'datainfo'=>$datainfo,
    //     'status' => 200
    // ]);
    
}





protected function fetchFreshData(){
    ini_set('max_execution_time', 300);

    $result=(new AliExpressApiService())->getInventory();
    dd($result);
    // $result=(new BestBuyApiService())->getInventory();
    
    // // Fetch fresh data from APIs
    // $delete=ProductStockMapping::truncate();
    // $shopifyInventoryData = (new ShopifyApiService())->getinventory();        
    // $parentskuList=$this->filterParentSKU($shopifyInventoryData);
    // $amazonInventoryData = (new AmazonSpApiService())->getinventory();
    // $walmartInventory=(new WalmartApiService())->getinventory();
    // $reverbInventory=(new ReverbApiService())->getInventory();
    // $sheinInventory = (new SheinApiService())->listAllProducts();
    // $dobaInventory = (new DobaApiService())->getinventory();
    // $temuInventory = (new TemuApiService())->getInventory();
    // $macyInventory = (new MacysApiService())->getInventory();
    // $ebay1Inventory = (new EbayApiService())->getEbayInventory();
    // $ebay2Inventory = (new Ebay2ApiService())->getEbayInventory();
    // $ebay3Inventory = (new Ebay3ApiService())->getEbayInventory();
    // $data = ProductStockMapping::all();
    // return $data;
}


protected function fetchFreshDataU($type = null)
{
    //    $result=(new AliExpressApiService())->getInventory();
    // return($result);
    ini_set('max_execution_time', 1800);
    $progress = [];
      // Define all sources including Shopify
    $sources = [
        'shopify' => fn() => (new ShopifyApiService())->getinventory(),
        'amazon'  => fn() => (new AmazonSpApiService())->getinventory(),
        'walmart' => fn() => (new WalmartApiService())->getinventory(),
        'reverb'  => fn() => (new ReverbApiService())->getInventory(),
        'shein'   => fn() => (new SheinApiService())->listAllProducts(),
        'doba'    => fn() => (new DobaApiService())->getinventory(),
        'temu'    => fn() => (new TemuApiService())->getInventory(),
        'macy'    => fn() => (new MacysApiService())->getInventory(),
        'ebay1'   => fn() => (new EbayApiService())->getEbayInventory(),
        'ebay2'   => fn() => (new Ebay2ApiService())->getEbayInventory(),
        'ebay3'   => fn() => (new Ebay3ApiService())->getEbayInventory(),
        'bestbuy'   => fn() => (new BestBuyApiService())->getInventory(),
        'tiendamia'   => fn() => (new TiendamiaApiService())->getInventory(),
    ];
   
    if (!$type) {
        ini_set('max_execution_time', 1800);
        ProductStockMapping::truncate();

        // Step 1: Fetch Shopify and filter parent SKUs
        $shopifyInventoryData = $this->safeFetch($sources['shopify'], 'shopify', $progress);
       
        $parentskuList = $this->filterParentSKU($shopifyInventoryData);

        // Step 2: Fetch all other platforms
        foreach ($sources as $platform => $fetcher) {
            if ($platform !== 'shopify') {
                $this->safeFetch($fetcher, $platform, $progress);
            }
        }

        \Log::info('Inventory sync progress:', $progress);

        return [
            'data' => ProductStockMapping::all(),
            'progress' => $progress
        ];
    } else {
        ini_set('max_execution_time', 2800);

        if (!array_key_exists($type, $sources)) {
            return ['status' => false, 'msg' => "Invalid type: $type"];
        }
     
        $result = $this->safeFetch($sources[$type], $type, $progress);
        // Optional: handle Shopify-specific logic if needed
        // if ($type === 'shopify') {
        //     $parentskuList = $this->filterParentSKU($result);
        // }
        
        return [
            'status' => true,
            'msg' => 'success',
            'progress' => $progress,
            'data' => $result
        ];
    }
}

protected function safeFetch(callable $fetcher, string $platform, array &$progress)
{

    try {
        $result = $fetcher();
        $progress[$platform] = 'Completed at ' . now()->toDateTimeString();
        return $result;
    } catch (\Throwable $e) {
        \Log::error("Failed to fetch $platform inventory: " . $e->getMessage());
        $progress[$platform] = 'Failed at ' . now()->toDateTimeString();
        return null;
    }
}




protected function fetchFreshData1111(){
    ini_set('max_execution_time', 1500);
     
    //   $result = (new BestbuyusaApiService())->getChannels();
    //   dd($result);
    //  $result = (new WayfairApiService())->getInventory();    
    
    // $macyInventory = (new MacysApiService())->getInventory();
    // die();

    // Fetch fresh data from APIs
    $delete=ProductStockMapping::truncate();
    $shopifyInventoryData = (new ShopifyApiService())->getinventory();        
    $parentskuList=$this->filterParentSKU($shopifyInventoryData);
    $amazonInventoryData = (new AmazonSpApiService())->getinventory();
    $walmartInventory=(new WalmartApiService())->getinventory();
    $reverbInventory=(new ReverbApiService())->getInventory();
    $sheinInventory = (new SheinApiService())->listAllProducts();
    $dobaInventory = (new DobaApiService())->getinventory();
    $temuInventory = (new TemuApiService())->getInventory();
    $macyInventory = (new MacysApiService())->getInventory();
    $ebay1Inventory = (new EbayApiService())->getEbayInventory();
    $ebay2Inventory = (new Ebay2ApiService())->getEbayInventory();
    $ebay3Inventory = (new Ebay3ApiService())->getEbayInventory();
      $data = ProductStockMapping::all();
    return $data;
}


protected function getDataInfo($data){
    
    $info = [
        'shopify' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'amazon' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
         'walmart' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'reverb' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'shein' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'doba' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'temu' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],

        'macy' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'ebay1' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'ebay2' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
        'ebay3' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
          'bestbuy' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
          'tiendamia' => [
            'listed' => 0,
            'notlisted' => 0,
            'nrl'=>0,
        ],
    ];

    foreach ($data as $item) {
        if($item['inventory_shopify']=='Not Listed'){ $info['shopify']['notlisted']++;}
        else if($item['inventory_shopify']=='NRL'){ $info['shopify']['nrl']++;}
        else{ $info['shopify']['listed']++;  }

        if($item['inventory_amazon']=='Not Listed'){  $info['amazon']['notlisted']++; }
        else if($item['inventory_amazon']=='NRL'){ $info['amazon']['nrl']++;}
        else {  $info['amazon']['listed']++; }

        if($item['inventory_walmart']=='Not Listed'){  $info['walmart']['notlisted']++; }
        else if($item['inventory_walmart']=='NRL'){ $info['walmart']['nrl']++;}
        else {  $info['walmart']['listed']++; }

        if($item['inventory_reverb']=='Not Listed'){  $info['reverb']['notlisted']++; }
        else if($item['inventory_reverb']=='NRL'){ $info['reverb']['nrl']++;}
        else {  $info['reverb']['listed']++; }

        if($item['inventory_shein']=='Not Listed'){  $info['shein']['notlisted']++; }
        else if($item['inventory_shein']=='NRL'){ $info['shein']['nrl']++;}
        else {  $info['shein']['listed']++; }

        if($item['inventory_doba']=='Not Listed'){  $info['doba']['notlisted']++; }
        else if($item['inventory_doba']=='NRL'){ $info['doba']['nrl']++;}
        else {  $info['doba']['listed']++; }

        if($item['inventory_temu']=='Not Listed'){  $info['temu']['notlisted']++; }
        else if($item['inventory_temu']=='NRL'){ $info['temu']['nrl']++;}
        else {  $info['temu']['listed']++; }
        
        if($item['inventory_macy']=='Not Listed'){  $info['macy']['notlisted']++; }
        else if($item['inventory_macy']=='NRL'){ $info['macy']['nrl']++;}
        else {  $info['macy']['listed']++; }
        
        if($item['inventory_ebay1']=='Not Listed'){  $info['ebay1']['notlisted']++; }
        else if($item['inventory_ebay1']=='NRL'){ $info['ebay1']['nrl']++;}
        else {  $info['ebay1']['listed']++; }

        if($item['inventory_ebay2']=='Not Listed'){  $info['ebay2']['notlisted']++; }
        else if($item['inventory_ebay2']=='NRL'){ $info['ebay2']['nrl']++;}
        else {  $info['ebay2']['listed']++; }

        if($item['inventory_ebay3']=='Not Listed'){  $info['ebay3']['notlisted']++; }
        else if($item['inventory_ebay3']=='NRL'){ $info['ebay3']['nrl']++;}
        else {  $info['ebay3']['listed']++; }

         if($item['inventory_bestbuy']=='Not Listed'){  $info['bestbuy']['notlisted']++; }
        else if($item['inventory_bestbuy']=='NRL'){ $info['bestbuy']['nrl']++;}
        else {  $info['bestbuy']['listed']++; }

         if($item['inventory_tiendamia']=='Not Listed'){  $info['tiendamia']['notlisted']++; }
        else if($item['inventory_tiendamia']=='NRL'){ $info['tiendamia']['nrl']++;}
        else {  $info['tiendamia']['listed']++; }

        // $shopifyQty = $item['inventory_shopify'] ?? null;
    
        // $amazonQty = $item['inventory_amazon'] ?? null;
        // $walmartQty = $item['inventory_walmart'] ?? null;
        // $reverbQty = $item['inventory_reverb'] ?? null;
        // $sheinQty = $item['inventory_shein'] ?? null;
        // $dobaQty = $item['inventory_doba'] ?? null;
        // $temuQty = $item['inventory_temu'] ?? null;
        // $macyQty = $item['inventory_macy'] ?? null;
        // $ebay1Qty = $item['inventory_ebay1'] ?? null;
        // $ebay2Qty = $item['inventory_ebay2'] ?? null;
        // $ebay3Qty = $item['inventory_ebay3'] ?? null;

        // $isShopifyListed = is_numeric($shopifyQty);
        // $isAmazonListed = is_numeric($amazonQty);
        // $isWalmartListed = is_numeric($walmartQty);
        // $isReverbListed = is_numeric($reverbQty);
        // $isSheinListed = is_numeric($sheinQty);
        // $isDobaListed = is_numeric($dobaQty);
        // $isTemuListed = is_numeric($temuQty);
        // $isMacyListed = is_numeric($macyQty);
        // $isEbay1Listed = is_numeric($ebay1Qty);
        // $isEbay2Listed = is_numeric($ebay2Qty);
        // $isEbay3Listed = is_numeric($ebay3Qty);

        // // Channel-specific listing status
        // if($isShopifyListed && $isShopifyListed=='Not Listed'  && $isShopifyListed==null){ $info['shopify']['notlisted']++; } else{$info['shopify']['listed']++;}
        // if($isAmazonListed && $isAmazonListed=='Not Listed'  && $isAmazonListed==null){ $info['amazon']['notlisted']++; } else{$info['amazon']['listed']++;}
        // if($isWalmartListed && $isWalmartListed=='Not Listed'  && $isWalmartListed==null){ $info['walmart']['notlisted']++; } else{$info['walmart']['listed']++;}
        // if($isReverbListed && $isReverbListed=='Not Listed'  && $isReverbListed==null){ $info['reverb']['notlisted']++; } else{$info['reverb']['listed']++;}
        // if($isSheinListed && $isSheinListed=='Not Listed'  && $isSheinListed==null){ $info['shein']['notlisted']++; } else{$info['shein']['listed']++;}
        // if($isDobaListed && $isDobaListed=='Not Listed'  && $isDobaListed==null){ $info['doba']['notlisted']++; } else{$info['doba']['listed']++;}
        // if($isTemuListed && $isTemuListed=='Not Listed'  && $isTemuListed==null){ $info['temu']['notlisted']++; } else{$info['temu']['listed']++;}
        // if($isMacyListed && $isMacyListed=='Not Listed'  && $isMacyListed==null){ $info['macy']['notlisted']++; } else{$info['macy']['listed']++;}
        // if($isEbay1Listed && $isEbay1Listed=='Not Listed'  && $isEbay1Listed==null){ $info['ebay1']['notlisted']++; } else{$info['ebay1']['listed']++;}
        // if($isEbay2Listed && $isEbay2Listed=='Not Listed'  && $isEbay2Listed==null){ $info['ebay2']['notlisted']++; } else{$info['ebay2']['listed']++;}
        // if($isEbay3Listed && $isEbay3Listed=='Not Listed'  && $isEbay3Listed==null){ $info['ebay3']['notlisted']++; } else{$info['ebay3']['listed']++;}

        // $info['shopify'][$isShopifyListed='Not Listed'  ? 'listed' : 'notlisted']++;
        // $info['amazon'][$isAmazonListed ? 'listed' : 'notlisted']++;
        // $info['walmart'][$isWalmartListed ? 'listed' : 'notlisted']++;
        // $info['reverb'][$isReverbListed ? 'listed' : 'notlisted']++;
        // $info['shein'][$isSheinListed ? 'listed' : 'notlisted']++;
        // $info['doba'][$isDobaListed ? 'listed' : 'notlisted']++;
        // $info['temu'][$isTemuListed ? 'listed' : 'notlisted']++;
        // $info['macy'][$isMacyListed ? 'listed' : 'notlisted']++;
        // $info['ebay1'][$isEbay1Listed ? 'listed' : 'notlisted']++;
        // $info['ebay2'][$isEbay2Listed ? 'listed' : 'notlisted']++;
        // $info['ebay3'][$isEbay3Listed ? 'listed' : 'notlisted']++;        
    }

    return $info;
}


protected function filterParentSKU(array $data): array
{
    // Extract SKUs from input array
    $filteredSkus = array_values(array_filter(array_map(function ($item) {
        return $item['sku'] ?? null;
    }, $data)));

    // Query ProductMaster for matching SKUs
    $parentRecords = ProductMaster::whereIn('sku', $filteredSkus)->get();

    // Return associative array: [sku => parent]
    return $parentRecords->pluck('parent', 'sku')->toArray();
}


    

    protected function getAllInventoryDataebay(){
        return $result = (new EbayApiService())->getEbayInventory();
    }
    
    public function WalmartInventoryData(){
        return $result = (new WalmartService())->getAllInventoryData();
    }

    public function getReverbInventoryData(){
        return $result = (new ReverbApiService())->getInventory();
    }

    protected function updateNotRequired(Request $request)
    {
       $not_required = $request->input('notrequired');
           foreach ($not_required as $entry) {
        [$sku, $id] = explode('___', $entry);

        ProductStockMapping::where('sku', $sku)
            ->where('id', $id)
            ->update(['not_required' => 1]); // or true, or any value you need
    }
        return response()->json(['status' => 'success']);
    }


    public function refetchLiveData(){
        $freshData=$this->fetchFreshData();   
        if($freshData){
            return response()->json(['status' => 'success']);
        }
    }

    public function refetchLiveDataU(Request $request){        
        $freshData=$this->fetchFreshDataU($request->source);   
        return $freshData;
        if($freshData){
            return response()->json(['status' => 'success']);
        }
    }

 
     public function getAccessTokenV1()
    {
        $res = Http::withoutVerifying()->asForm()->post('https://api.amazon.com/auth/o2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => env('SPAPI_REFRESH_TOKEN'),
            'client_id' => env('SPAPI_CLIENT_ID'),
            'client_secret' => env('SPAPI_CLIENT_SECRET'),
        ]);

        return $res['access_token'] ?? null;
    }


    public function getAmazonProductAndOffers($asin)
    {
        $marketplaceId = env('SPAPI_MARKETPLACE_ID'); // e.g. ATVPDKIKX0DER
        $accessToken   = $this->getAccessTokenV1(); // your function to get SP-API access token

        // Pricing/Offers Info (all sellers)
        $offersUrl = "https://sellingpartnerapi-na.amazon.com/products/pricing/v0/items/{$asin}/offers"
            . "?MarketplaceId={$marketplaceId}&ItemCondition=New";


        sleep(2);


        $offersRes = Http::withoutVerifying()->withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'x-amz-access-token' => $accessToken,
        ])->get($offersUrl)->json();

        dd($accessToken, $offersRes);


        $payload = $offersRes['payload'] ?? null;

        if (!$payload || empty($payload['Offers'])) {
            return [
                'asin'     => $asin,
                'price'    => null,
                'shipping' => null,
                'seller'   => null,
                'image'    => null,
            ];
        }

        $firstOffer = $payload['Offers'][0]; // take first seller (often BuyBox winner)

        return [
            'asin'     => $asin,
            'price'    => $firstOffer['ListingPrice']['Amount'] ?? null,
            'shipping' => $firstOffer['Shipping']['Amount'] ?? null,
            'seller'   => $firstOffer['SellerId'] ?? null,
            // For image, Pricing API doesn’t return it → keeping null placeholder
            'image'    => null,
        ];
    }
    

    }

    
