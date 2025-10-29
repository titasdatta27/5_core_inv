<?php

namespace App\Http\Controllers\ProductMaster;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\AmazonDatasheet;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\JungleScoutProductData;
use App\Models\Supplier;
use App\Models\TransitContainerDetail;

class ForecastAnalysisController extends Controller
{
    protected $apiController;

    public function __construct(ApiController $apiController)
    {
        $this->apiController = $apiController;
    }

    private function buildForecastAnalysisData()
    {
        $normalizeSku = fn($sku) => strtoupper(trim($sku));

        $jungleScoutData = JungleScoutProductData::query()
            ->get()
            ->groupBy(fn($item) => $normalizeSku($item->parent))
            ->map(function ($group) {
                $validPrices = $group->filter(function ($item) {
                    $data = is_array($item->data) ? $item->data : [];
                    $price = $data['price'] ?? null;
                    return is_numeric($price) && $price > 0;
                })->pluck('data.price');

                return [
                    'scout_parent' => $group->first()->parent,
                    'min_price' => $validPrices->isNotEmpty() ? $validPrices->min() : null,
                    'product_count' => $group->count(),
                    'all_data' => $group->map(function ($item) {
                        $data = is_array($item->data) ? $item->data : [];
                        if (isset($data['price'])) {
                            $data['price'] = is_numeric($data['price']) ? (float) $data['price'] : null;
                        }
                        return $data;
                    })->toArray()
                ];
            });

        $productListData = DB::table('product_master')->get();

        $shopifyData = ShopifySku::all()->keyBy(fn($item) => $normalizeSku($item->sku));


        $supplierRows = Supplier::where('type', 'Supplier')->get();
        $supplierMapByParent = [];
        foreach ($supplierRows as $row) {
            $parents = array_map('trim', explode(',', strtoupper($row->parent ?? '')));
            foreach ($parents as $parent) {
                if (!empty($parent)) {
                    $supplierMapByParent[$parent][] = $row->name;
                }
            }
        }

        $forecastMap = DB::table('forecast_analysis')->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $movementMap = DB::table('movement_analysis')->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $readyToShipMap = DB::table('ready_to_ship')->where('transit_inv_status', 0)->whereNull('deleted_at')->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $mfrg = DB::table('mfrg_progress')->get()->keyBy(fn($item) => $normalizeSku($item->sku));
        $purchases = DB::table('purchases')
            ->select('items')
            ->get()
            ->flatMap(function ($row) {
                $items = json_decode($row->items);

                if (!is_array($items)) return [];
                
                return collect($items)->mapWithKeys(function ($item) {
                    if (!isset($item->sku)) return [];
                    return [$item->sku => $item];
                });
            });

        $transitContainer = TransitContainerDetail::whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('status')
                ->orWhere('status', '');
            })
            ->select('our_sku', 'tab_name', 'no_of_units', 'total_ctn', 'rate')
            ->get()
            ->groupBy(fn($item) => strtoupper(trim($item->our_sku)))
            ->map(function ($group) {
                $transitSum = 0;
                $rate = 0;
                foreach ($group as $row) {
                    $no_of_units = (float) $row->no_of_units;
                    $total_ctn = (float) $row->total_ctn;
                    $transitSum += $no_of_units * $total_ctn;
                    if (!empty($row->rate)) {
                        $rate = (float) $row->rate;
                    }
                }

                return (object)[
                    'tab_name' => $group->pluck('tab_name')->unique()->implode(', '),
                    'transit' => $transitSum,
                    'rate' => $rate,
                ];
            })
            ->keyBy(fn($item, $key) => $key);



        $processedData = [];

        foreach ($productListData as $prodData) {
            $sheetSku = $normalizeSku($prodData->sku);
            if (empty($sheetSku)) continue;

            $item = new \stdClass();
            $item->SKU = $sheetSku;
            $item->Parent = $normalizeSku($prodData->parent ?? '');
            $item->is_parent = stripos($sheetSku, 'PARENT') !== false;
            $item->{'Supplier Tag'} = isset($supplierMapByParent[$item->Parent]) ? implode(', ', array_unique($supplierMapByParent[$item->Parent])) : '';

            $valuesRaw = $prodData->Values ?? '{}';
            $values = json_decode($valuesRaw, true);

            $item->{'CP'} = $values['cp'] ?? '';
            $item->{'LP'} = $values['lp'] ?? '';
            $item->{'MOQ'} = $values['moq'] ?? '';
            $item->{'SH'} = $values['ship'] ?? '';
            $item->{'Freight'} = $values['frght'] ?? '';
            $item->{'CBM MSL'} = $values['cbm'] ?? '';
            $item->{'GW (LB)'} = $values['wt_act'] ?? '';
            $item->{'GW (KG)'} = is_numeric($values['wt_act'] ?? null) ? round($values['wt_act'] * 0.45, 2) : '';

            $shopify = $shopifyData[$sheetSku] ?? null;
            $imageFromShopify = $shopify->image_src ?? null;
            $imageFromProductMaster = $values['image_path'] ?? null;
            $item->Image = $imageFromShopify ?: $imageFromProductMaster;

            $item->INV = $shopify->inv ?? 0;
            $item->L30 = $shopify->quantity ?? 0;
            
            // Calculate shopifyb2c_price and inv_value
            $shopifyb2c_price = $shopify->price ?? 0;
            $item->shopifyb2c_price = $shopifyb2c_price;
            $item->inv_value = $item->INV * $shopifyb2c_price;
            
            // Calculate lp_value (LP * INV)
            $lp = is_numeric($item->{'LP'}) ? (float)$item->{'LP'} : 0;
            $item->lp_value = $lp * $item->INV;

            // if (!empty($item->Parent) && $jungleScoutData->has($item->Parent)) {
            //     $item->scout_data = json_decode(json_encode($jungleScoutData[$item->Parent]), true);
            // }

            if ($forecastMap->has($sheetSku)) {
                $forecast = $forecastMap->get($sheetSku);
                $item->{'s-msl'} = $forecast->s_msl ?? 0;
                $item->{'Approved QTY'} = $forecast->approved_qty ?? 0;
                $item->nr = $forecast->nr ?? '';
                $item->req = $forecast->req ?? '';
                $item->hide = $forecast->hide ?? '';
                $item->notes = $forecast->notes ?? '';
                $item->{'Clink'} = $forecast->clink ?? '';
                $item->{'Olink'} = $forecast->olink ?? '';
                $item->rfq_form_link = $forecast->rfq_form_link ?? '';
                $item->rfq_report = $forecast->rfq_report ?? '';
                $item->date_apprvl = $forecast->date_apprvl ?? '';
                $item->stage = $forecast->stage ?? '';
            }

            $item->containerName = $transitContainer[$normalizeSku($prodData->sku)]->tab_name ?? '';
            $item->transit = $transitContainer[$normalizeSku($prodData->sku)]->transit ?? 0;


            $readyToShipQty = 0;
            if($readyToShipMap->has($sheetSku)){
                $readyToShipQty = $readyToShipMap->get($sheetSku)->qty ?? 0;
                $item->readyToShipQty = $readyToShipQty;
            }

            $order_given = 0;
            if($mfrg->has($sheetSku)){
                $isReadyToShip = $mfrg->get($sheetSku)->ready_to_ship ?? 'No';
                if($isReadyToShip === 'No' || $isReadyToShip === ''){
                    $order_given = (float) ($mfrg->get($sheetSku)->qty ?? 0);
                }
            }

            if ($purchases->has($sheetSku)) {
                $p = $purchases->get($sheetSku);
                $purchase_qty = (float)($p->qty ?? 0);
                if ($purchase_qty > 0) {
                    $order_given = $purchase_qty;
                }
            }
            $item->order_given = $order_given;

            if ($movementMap->has($sheetSku)) {
                $months = json_decode($movementMap->get($sheetSku)->months ?? '{}', true);
                $months = is_array($months) ? $months : [];

                $monthNames = ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
                $totalMonthCount = 0;
                $totalSum = 0;

                foreach ($monthNames as $month) {
                    $value = isset($months[$month]) && is_numeric($months[$month]) ? (int)$months[$month] : 0;
                    $item->{$month} = $value;
                    if ($value !== 0) $totalMonthCount++;
                    $totalSum += $value;
                }

                $item->{'Total'} = $totalSum;
                $item->{'Total month'} = $totalMonthCount;
                
                $msl = $item->{'Total month'} > 0 ? ($item->{'Total'} / $item->{'Total month'}) * 4 : 0;

                $effectiveMsl = (isset($item->{'s-msl'}) && $item->{'s-msl'} > 0) ? $item->{'s-msl'} : $msl;
                
                $lp = is_numeric($item->{'LP'}) ? (float)$item->{'LP'} : 0;
                $item->{'MSL_C'} = round($msl * $lp / 4, 2);

                $mslfour = $msl/4;

                $item->{'MSL_Four'} = round($msl / 4, 2);

                $item->{'MSL_SP'} = floor($shopifyb2c_price * $effectiveMsl / 4);
            }

            $cp = (float)($item->{'CP'} ?? 0);
            $orderQty = (float)($item->order_given ?? 0);
            $readyToShipQty = (float)($item->readyToShipQty ?? 0);
            $transit = (float)($transitContainer[$normalizeSku($prodData->sku)]->transit ?? 0);

            $item->MIP_Value = round($cp * $orderQty, 2);
            $item->R2S_Value = round($cp * $readyToShipQty, 2);
            $item->Transit_Value = round($cp * $transit, 2);

            $processedData[] = $item;
        }

        return $processedData;
    }

    public function getViewForecastAnalysisData()
    {
        try {
            $processedData = $this->buildForecastAnalysisData();

            $totalMslC = collect($processedData)
                ->filter(function ($item) {
                    return !$item->is_parent;
                })
                ->sum(function ($item) {
                    return floatval($item->{'MSL_C'} ?? 0);
                });

            return response()->json([
                'message' => 'Data fetched successfully',
                'data' => $processedData,
                'total_msl_c' => round($totalMslC, 2),
                'status' => 200,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forecastAnalysis(Request $request)
    {
        $mode = $request->query('mode');
        $demo = $request->query('demo');

        return view('purchase-master.forecastAnalysis', [
            'mode' => $mode,
            'demo' => $demo,
        ]);
    }

    public function updateForcastSheet(Request $request)
    {        
        $sku = trim($request->input('sku'));
        $parent = trim($request->input('parent'));
        $column = trim($request->input('column'));
        $value = $request->input('value');

        $columnMap = [
            'S-MSL' => 's_msl',
            'Approved QTY' => 'approved_qty',
            'NR' => 'nr',
            'REQ' => 'req',
            'Hide' => 'hide',
            'Notes' => 'notes',
            'Clink' => 'clink',
            'Olink' => 'olink',
            'rfq_form_link' => 'rfq_form_link',
            'rfq_report' => 'rfq_report',
            'order_given' => 'order_given',
            'Transit' => 'transit',
            'Date of Appr' => 'date_apprvl',
            'Stage' => 'stage',
        ];

        $columnKey = $columnMap[$column] ?? null;

        if (!$columnKey) {
            return response()->json(['success' => false, 'message' => 'Invalid column']);
        }

        $existing = DB::table('forecast_analysis')
            ->select('*')
            ->whereRaw('TRIM(LOWER(sku)) = ?', [strtolower($sku)])
            ->whereRaw('TRIM(LOWER(parent)) = ?', [strtolower($parent)])
            ->first();

        if ($existing) {
            $currentValue = $existing->{$columnKey} ?? null;

            if ((string)$currentValue !== (string)$value) {
                DB::table('forecast_analysis')
                    ->where('id', $existing->id)
                    ->update([$columnKey => $value, 'updated_at' => now()]);
            }

            if (strtolower($column) === 'stage'){
                $orderQty = $existing->approved_qty ?? null;
                if(strtolower($value) === 'to_order_analysis'){
                    DB::table('to_order_analysis')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'approved_qty' => $orderQty,
                            'date_apprvl' => now()->toDateString(),
                            'stage' => '',
                            'auth_user' => Auth::user()->name,
                            'updated_at' => now(),
                            'created_at' => now(),
                            'deleted_at' => null,
                        ]
                    );
                }

                if(strtolower($value) === 'mip'){
                    DB::table('mfrg_progress')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'qty' => $orderQty,
                            'ready_to_ship' => 'No',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                if(strtolower($value) === 'r2s'){
                    DB::table('ready_to_ship')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'qty' => $orderQty,
                            'transit_inv_status' => 0,
                            'auth_user' => Auth::user()->name,
                            'updated_at' => now(),
                            'created_at' => now(),
                            'deleted_at' => null,
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Updated or already up-to-date']);
        } else {
            DB::table('forecast_analysis')->insert([
                'sku' => $sku,
                'parent' => $parent,
                $columnKey => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (strtolower($column) === 'stage'){
                $orderQty = DB::table('forecast_analysis')
                    ->whereRaw('TRIM(LOWER(sku)) = ?', [strtolower($sku)])
                    ->whereRaw('TRIM(LOWER(parent)) = ?', [strtolower($parent)])
                    ->value('order_given');
                    
                if(strtolower($value) === 'to_order_analysis'){
                    DB::table('to_order_analysis')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'approved_qty' => $orderQty,
                            'date_apprvl' => now()->toDateString(),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                if(strtolower($value) === 'mip'){
                    DB::table('mfrg_progress')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'qty' => $orderQty,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                if(strtolower($value) === 'r2s'){
                    DB::table('ready_to_ship')->updateOrInsert(
                        ['sku' => $sku, 'parent' => $parent],
                        [
                            'qty' => $orderQty,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Inserted new row']);
        }
    }

    public function invetoryStagesView(){
        return view('purchase-master.inventory-stages');
    }

    public function invetoryStagesData(){
        try {
            $normalizeSku = fn($sku) => strtoupper(trim(preg_replace('/\s+/',' ', str_replace("\xc2\xa0",' ',$sku))));

            $jungleScoutData = JungleScoutProductData::query()
                ->get()
                ->groupBy(fn($item) => strtoupper(trim($item->parent)))
                ->map(function ($group) {
                    $validPrices = $group->filter(function ($item) {
                        $data = is_array($item->data) ? $item->data : [];
                        $price = $data['price'] ?? null;
                        return is_numeric($price) && $price > 0;
                    })->pluck('data.price');

                    return [
                        'scout_parent' => $group->first()->parent,
                        'min_price' => $validPrices->isNotEmpty() ? $validPrices->min() : null,
                        'product_count' => $group->count(),
                        'all_data' => $group->map(function ($item) {
                            $data = is_array($item->data) ? $item->data : [];
                            if (isset($data['price'])) $data['price'] = is_numeric($data['price']) ? (float)$data['price'] : null;
                            return $data;
                        })->toArray()
                    ];
                });

            // Product Master
            $productListData = DB::table('product_master')->get();

            $productListDataBySku = $productListData->keyBy(fn($item) => $normalizeSku($item->sku));

            $skus = $productListData->pluck('sku')->filter()->unique()->map($normalizeSku)->toArray();

            // Shopify Data
            $shopifyData = ShopifySku::whereIn(DB::raw('UPPER(TRIM(REPLACE(sku,"  "," ")))'), $skus)
                ->get()
                ->keyBy(fn($item) => $normalizeSku($item->sku));

            // Suppliers
            $supplierRows = Supplier::where('type','Supplier')->get();

            $supplierMapByParent = [];
            foreach($supplierRows as $row){
                $parents = array_map('trim', explode(',', strtoupper($row->parent ?? '')));
                foreach($parents as $parent){
                    if(!empty($parent)){
                        $supplierMapByParent[$parent][] = $row->name;
                    }
                }
            }

            // Forecast, Movement, Mfrg, ReadyToShip
            $forecastMap = DB::table('forecast_analysis')->get()->keyBy(fn($item) => strtoupper(trim($item->parent)) . '|' . strtoupper(trim($item->sku)));

            $movementMap = DB::table('movement_analysis')->get()->keyBy(fn($item) => strtoupper(trim($item->sku)));

            $mfrgProgressMap = DB::table('mfrg_progress')
                ->select('sku', DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(ready_to_ship) as ready_to_ship'))
                ->groupBy('sku')
                ->get()
                ->keyBy(fn($item) => strtoupper(trim($item->sku)));
            
            $readyToShip = DB::table('ready_to_ship')->select('sku', DB::raw('SUM(qty) as total_qty'))->groupBy('sku')->get()->keyBy(fn($item) => strtoupper(trim($item->sku)));
            
            $toOrderMap = DB::table('to_order_analysis')->select('sku', 'stage')->get()->keyBy(fn($item) => strtoupper(trim($item->sku)));
            $transitContainer = DB::table('transit_container_details')->where('status', '')->select('our_sku', 'tab_name', 'no_of_units', 'total_ctn')->get()->keyBy(fn($item) => strtoupper(trim($item->our_sku)));
            
            $readyToShipMap = DB::table('ready_to_ship')->get()->keyBy(fn($item) => $normalizeSku($item->sku));

            $processedData = [];

            foreach($productListDataBySku as $sheetSku => $prodData){
                $item = new \stdClass();
                $item->SKU = $prodData->sku;
                $item->Parent = strtoupper(trim($prodData->parent ?? ''));
                $item->is_parent = stripos($prodData->sku,'PARENT') !== false;
                $item->{'Supplier Tag'} = isset($supplierMapByParent[$item->Parent]) ? implode(', ', array_unique($supplierMapByParent[$item->Parent])) : '';

                $valuesRaw = $prodData->Values ?? '{}';
                $values = json_decode($valuesRaw, true);

                $item->{'CP'} = $values['cp'] ?? '';
                $item->{'LP'} = $values['lp'] ?? '';
                $item->{'MOQ'} = $values['MOQ'] ?? '';
                $item->{'SH'} = $values['ship'] ?? '';
                $item->{'Freight'} = $values['frght'] ?? '';
                $item->{'CBM MSL'} = $values['cbm'] ?? '';
                $item->{'GW (LB)'} = $values['wt_act'] ?? '';
                $item->{'GW (KG)'} = is_numeric($values['wt_act'] ?? null) ? round($values['wt_act']*0.45,2) : '';

                // Image
                $normalizedSku = $normalizeSku($prodData->sku);
                $shopify = $shopifyData[$normalizedSku] ?? null;
                $imageFromShopify = $shopify->image_src ?? null;
                $imageFromProductMaster = $values['image_path'] ?? null;
                $item->Image = $imageFromShopify ?: $imageFromProductMaster;

                $item->INV = $shopify->inv ?? 0;
                $item->L30 = $shopify->quantity ?? 0;
                
                // Calculate shopifyb2c_price and inv_value
                $shopifyb2c_price = $shopify->price ?? 0;
                $item->shopifyb2c_price = $shopifyb2c_price;
                $item->inv_value = $item->INV * $shopifyb2c_price;
                
                // Calculate lp_value (LP * INV)
                $lp = is_numeric($item->{'LP'}) ? (float)$item->{'LP'} : 0;
                $item->lp_value = $lp * $item->INV;

                // JungleScout
                // if(!empty($item->Parent) && $jungleScoutData->has($item->Parent)){
                //     $item->scout_data = json_decode(json_encode($jungleScoutData[$item->Parent]), true);
                // }

                // Forecast
                $forecastKey = $item->Parent.'|'.$prodData->sku;
                if($forecastMap->has($forecastKey)){
                    $forecast = $forecastMap->get($forecastKey);
                    $item->{'s-msl'} = $forecast->s_msl ?? '';
                    $item->{'Approved QTY'} = $forecast->approved_qty ?? '';
                    $item->order_given = $mfrgProgressMap[strtoupper(trim($prodData->sku))]->total_qty ?? 0;
                    $item->nr = $forecast->nr ?? '';
                    $item->req = $forecast->req ?? '';
                    $item->hide = $forecast->hide ?? '';
                    $item->notes = $forecast->notes ?? '';
                    $item->{'Clink'} = $forecast->clink ?? '';
                    $item->{'Olink'} = $forecast->olink ?? '';
                    $item->rfq_form_link = $forecast->rfq_form_link ?? '';
                    $item->rfq_report = $forecast->rfq_report ?? '';
                    $item->date_apprvl = $forecast->date_apprvl ?? '';
                }

                $item->containerName = $transitContainer[strtoupper(trim($prodData->sku))]->tab_name ?? '';
                $noOfUnit = $transitContainer[strtoupper(trim($prodData->sku))]->no_of_units ?? 0;
                $totalCtn = $transitContainer[strtoupper(trim($prodData->sku))]->total_ctn	 ?? 0;
                $item->c_sku_qty = $noOfUnit * $totalCtn;

                if($readyToShipMap->has($sheetSku)){
                    $item->readyToShipQty = $readyToShipMap->get($sheetSku)->qty ?? 0;
                }
                    
                // Movement
                if($movementMap->has(strtoupper(trim($prodData->sku)))){
                    $months = json_decode($movementMap[strtoupper(trim($prodData->sku))]->months ?? '{}',true);
                    $months = is_array($months)?$months:[];
                    $monthNames = ['Dec','jan','feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov'];
                    $totalMonthCount = 0; $totalSum=0;
                    foreach($monthNames as $month){
                        $value = isset($months[$month]) && is_numeric($months[$month]) ? (int)$months[$month]:0;
                        $item->{$month} = $value;
                        if($value!==0) $totalMonthCount++;
                        $totalSum += $value;
                    }
                    $item->{'Total'} = $totalSum;
                    $item->{'Total month'} = $totalMonthCount;
                    
                    // Calculate MSL
                    $msl = $item->{'Total month'} > 0 ? ($item->{'Total'} / $item->{'Total month'}) * 4 : 0;
                    
                    // Calculate MSL_C (MSL * LP)
                    $lp = is_numeric($item->{'LP'}) ? (float)$item->{'LP'} : 0;
                    $item->{'MSL_C'} = (int)round($msl * $lp);
                    
                    // Calculate MSL SP (shopify price * MSL / 4)
                    $item->{'MSL_SP'} = floor($shopifyb2c_price * $msl / 4);
                }
                $skuKey = strtoupper(trim($prodData->sku));

                if ($toOrderMap->has($skuKey)) {
                    $stage = trim($toOrderMap[$skuKey]->stage ?? '');

                    if ($stage === '' || $stage === null) {
                        if (isset($mfrgProgressMap[$skuKey]) && strtolower($mfrgProgressMap[$skuKey]->ready_to_ship ?? '') === 'yes') {
                            $skuStage = 'Ready To Ship';
                        } elseif (isset($readyToShip[$skuKey]) && ($readyToShip[$skuKey]->total_qty ?? 0) > 0) {
                            $skuStage = 'Ready To Ship';
                        } else {
                            $skuStage = '2 Order Analysis';
                        }
                    } elseif ($stage === 'Mfrg Progress') {
                        if (isset($mfrgProgressMap[$skuKey]) && strtolower($mfrgProgressMap[$skuKey]->ready_to_ship ?? '') === 'yes') {
                            $skuStage = 'Ready To Ship';
                        } elseif (isset($readyToShip[$skuKey]) && ($readyToShip[$skuKey]->total_qty ?? 0) > 0) {
                            $skuStage = 'Ready To Ship';
                        } else {
                            $skuStage = 'Mfrg Progress';
                        }
                    } else {
                        $skuStage = $stage;
                    }
                } else {
                    if (isset($mfrgProgressMap[$skuKey]) && strtolower($mfrgProgressMap[$skuKey]->ready_to_ship ?? '') === 'yes') {
                        $skuStage = 'Ready To Ship';
                    } elseif (isset($readyToShip[$skuKey]) && ($readyToShip[$skuKey]->total_qty ?? 0) > 0) {
                        $skuStage = 'Ready To Ship';
                    } else {
                        $skuStage = '2 Order Analysis';
                    }
                }

                $item->sku_stage = $skuStage;

                $processedData[] = $item;
            }

            return response()->json([
                'message'=>'Data fetched successfully',
                'data'=>$processedData,
                'status'=>200
            ]);

        }catch(\Throwable $e){
            return response()->json([
                'message'=>'Something went wrong!',
                'error'=>$e->getMessage()
            ],500);
        }
    }

}
