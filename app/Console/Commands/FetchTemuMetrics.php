<?php

namespace App\Console\Commands;

use App\Models\TemuMetric;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchTemuMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-temu-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {       
        $this->fetchSkus();
        $this->fetchQuantity();
        $this->fetchGoodsId();
        $this->fetchBasePrice();
        $this->fetchProductAnalyticsData();
    }

    private function fetchProductAnalyticsData(){

        $goodsIds = TemuMetric::pluck('goods_id')->toArray();

        $startTs = Carbon::yesterday()->startOfDay()->timestamp * 1000;
        $endTs = Carbon::yesterday()->endOfDay()->timestamp * 1000;

        $ranges = [
            'L30' => [
                'startTs' => Carbon::now()->subDays(30)->startOfDay()->timestamp * 1000,
                'endTs' => Carbon::yesterday()->endOfDay()->timestamp * 1000,
            ],
            'L60' => [
                'startTs' => Carbon::now()->subDays(60)->startOfDay()->timestamp * 1000,
                'endTs' => Carbon::now()->subDays(31)->endOfDay()->timestamp * 1000,
            ],
        ];


        foreach ($goodsIds as $goodId) {
            $metrics = [
                'product_impressions_l30' => 0,
                'product_clicks_l30' => 0,
                'product_impressions_l60' => 0,
                'product_clicks_l60' => 0,
            ];
            foreach ($ranges as $label => $range) {
                $requestBody = [
                    'type' => 'temu.searchrec.ad.reports.goods.query',
                    'goodsId' => $goodId,
                    'startTs' => $range['startTs'],
                    'endTs' => $range['endTs'],
                ];

                $signedRequest = $this->generateSignValue($requestBody);
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);

                if ($response->failed()) {
                    $this->error("Request failed for Goods ID: {$goodId} | " . $response->body());
                    continue;
                }

                $data = $response->json();
                if (!($data['success'] ?? false)) {
                    $this->error("Temu API error for Goods ID: {$goodId} | " . ($data['errorMsg'] ?? 'Unknown'));
                    continue;
                }

                $summary = $data['result']['reportInfo']['reportsSummary'] ?? null;

                if ($summary) {
                    if ($label === 'L30') {
                        $metrics['product_impressions_l30'] = $summary['imprCntAll']['val'] ?? 0;
                        $metrics['product_clicks_l30'] = $summary['clkCntAll']['val'] ?? 0;
                    } elseif ($label === 'L60') {
                        $metrics['product_impressions_l60'] = $summary['imprCntAll']['val'] ?? 0;
                        $metrics['product_clicks_l60'] = $summary['clkCntAll']['val'] ?? 0;
                    }
                }
            }

            TemuMetric::updateOrCreate(
                ['goods_id' => $goodId],
                $metrics
            );
        }

        $this->info("Analytics data updated successfully.");
    }

    // private function fetchBasePrice(){
    //     // Currently not any API provides base price.
    // }

    private function fetchBasePrice()
    {
        $skus = TemuMetric::pluck('sku_id')->toArray();

        foreach ($skus as $skuId) {
            $requestBody = [
                "type" => "bg.local.goods.sku.list.price.query",
                "skuIds" => [$skuId], // API ko array me SKU IDs bhejni hoti hain
            ];

            $signedRequest = $this->generateSignValue($requestBody);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);

            if ($response->failed()) {
                $this->error("Price request failed for SKU: {$skuId} | " . $response->body());
                continue;
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                $this->error("Temu Price API error for SKU: {$skuId} | " . ($data['errorMsg'] ?? 'Unknown'));
                continue;
            }

            $priceInfoList = $data['result']['skuPriceInfoList'] ?? [];
            if (empty($priceInfoList)) {
                $this->warn("No price info found for SKU: {$skuId}");
                continue;
            }

            $priceInfo = $priceInfoList[0];

            TemuMetric::where('sku_id', $skuId)->update([
                'base_price' => $priceInfo['basePrice'] ?? null,
                'currency'   => $priceInfo['currency'] ?? null,
                'price_last_updated' => now(),
            ]);

            $this->info("Price updated for SKU: {$skuId}");
        }

        $this->info("Base Prices updated successfully.");
    }

    public function fetchGoodsId(){
        $pageToken = null;
        do {
            $requestBody = [
                "type" => "temu.local.goods.list.retrieve",                
                "goodsSearchType" => "ALL",
                "pageSize" => 100,
            ];

            if ($pageToken) {
                $requestBody["pageToken"] = $pageToken;
            }

            $signedRequest = $this->generateSignValue($requestBody);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);

            if ($response->failed()) {
                $this->error("Request failed: " . $response->body());
                break;
            }

            $data = $response->json();
            
            if (!($data['success'] ?? false)) {
                $this->error("Temu Error: " . $data['errorMsg'] ?? 'Unknown');
                break;
            }

            $goodsList = $data['result']['goodsList'] ?? [];

            foreach ($goodsList as $good) {
                $goodsId = $good['goodsId'] ?? null;
                foreach ($good['skuInfoList'] ?? [] as $sku) {
                    $sku = $sku['skuSn'] ?? null;
                    
                    if ($sku && $goodsId) {
                        TemuMetric::where('sku', $sku)->update([
                            'goods_id' => $goodsId,
                        ]);
                    }
                }
            }

            $pageToken = $data['result']['pagination']['nextToken'] ?? null;

        } while ($pageToken);

        $this->info("Goods ID Updated Successfully.");
    }

    private function fetchQuantity(){
        // 🔹 Define dynamic L30 and L60 date ranges
        $today = Carbon::today();

        $toL30 = $today->copy()->subDay(); // e.g. June 1
        $fromL30 = $toL30->copy()->subDays(29); // e.g. May 3

        $toL60 = $fromL30->copy()->subDay(); // e.g. May 2
        $fromL60 = $toL60->copy()->subDays(29); // e.g. April 2

        $ranges = [
            'L30' => [$fromL30, $toL30],
            'L60' => [$fromL60, $toL60],
        ];

        $finalSkuQuantities = [];
        foreach($ranges as $label => [$from, $to]){
            $pageNumber = 1;
            $skuQuantitiesL30 = [];
            $skuQuantitiesL60 = [];
    
            do {
                $requestBody = [
                    "type" => "bg.order.list.v2.get",
                    "pageSize" => 100,
                    "pageNumber" => $pageNumber,
                    "createAfter" => $from->timestamp,     // ✅ UNIX timestamp
                    "createBefore" => $to->copy()->endOfDay()->timestamp, // ✅ End of day
                ];
    
                $signedRequest = $this->generateSignValue($requestBody);
    
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);
    
                if ($response->failed()) {
                    $this->error("Request failed: " . $response->body());
                    break;
                }
    
                $data = $response->json();
    
                if (!($data['success'] ?? false)) {
                    $this->error("Temu Error: " . ($data['errorMsg'] ?? 'Unknown'));
                    break;
                }
                $orders = $data['result']['pageItems'] ?? [];
                if (empty($orders)) break;
                    
                foreach ($orders as $order) {
                    
                    foreach ($order['orderList'] ?? [] as $item) {
                        $skuId = $item['skuId'];
                        $qty = $item['quantity'];

                        if (!isset($finalSkuQuantities[$skuId])) {
                            $finalSkuQuantities[$skuId] = ['quantity_purchased_l30' => 0, 'quantity_purchased_l60' => 0];
                        }
                        if ($label === 'L30') {
                            $finalSkuQuantities[$skuId]['quantity_purchased_l30'] += $qty;
                        } elseif ($label === 'L60') {
                            $finalSkuQuantities[$skuId]['quantity_purchased_l60'] += $qty;
                        }
                    }
                }
    
                $pageNumber++;
            } while (true);

            foreach ($finalSkuQuantities as $skuId => $data) {                
                TemuMetric::where('sku_id', $skuId)->update([
                    'quantity_purchased_l30' => $data['quantity_purchased_l30'],
                    'quantity_purchased_l60' => $data['quantity_purchased_l60'],
                ]);
            }
        }


        $this->info("Quantity Purchased Update Successfully.");
    }

    // private function fetchSkus(){
    //     $pageToken = null;
    //     do {
    //         $requestBody = [
    //             "type" => "temu.local.sku.list.retrieve",                
    //             "skuSearchType" => "ACTIVE",
    //             "pageSize" => 100,
    //         ];

    //         if ($pageToken) {
    //             $requestBody["pageToken"] = $pageToken;
    //         }

    //         $signedRequest = $this->generateSignValue($requestBody);

    //         $response = Http::withHeaders([
    //             'Content-Type' => 'application/json'
    //         ])->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);

    //         if ($response->failed()) {
    //             $this->error("Request failed: " . $response->body());
    //             break;
    //         }

    //         $data = $response->json();
            
    //         if (!($data['success'] ?? false)) {
    //             $this->error("Temu Error: " . $data['errorMsg'] ?? 'Unknown');
    //             break;
    //         }

    //         $skus = $data['result']['skuList'] ?? [];

    //         foreach ($skus as $sku) {
    //             TemuMetric::updateOrCreate(
    //                 ['sku' => $sku['outSkuSn'], 'sku_id' => $sku['skuId']],
    //             );
    //         }

    //         $pageToken = $data['result']['pagination']['nextToken'] ?? null;

    //     } while ($pageToken);

    //     $this->info("SKUs Synced Successfully.");
    // }

    private function fetchSkus()
    {
        $pageToken = null;
        $pageCount = 0;

        do {
            $requestBody = [
                "type" => "temu.local.sku.list.retrieve",                
                "skuSearchType" => "ACTIVE",
                "pageSize" => 50, // reduce size to avoid timeout
            ];

            if ($pageToken) {
                $requestBody["pageToken"] = $pageToken;
            }

            $signedRequest = $this->generateSignValue($requestBody);

            try {
                $response = Http::timeout(40) // ⏳ avoid hanging forever
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post('https://openapi-b-us.temu.com/openapi/router', $signedRequest);
            } catch (\Exception $e) {
                $this->error("HTTP Request Exception: " . $e->getMessage());
                break;
            }

            if ($response->failed()) {
                $this->error("Request failed: " . $response->body());
                break;
            }

            $data = $response->json();
            
            if (!($data['success'] ?? false)) {
                $this->error("Temu Error: " . ($data['errorMsg'] ?? 'Unknown'));
                break;
            }

            $skus = $data['result']['skuList'] ?? [];

            if (empty($skus)) {
                $this->warn("No SKUs found on page {$pageCount}");
                break;
            }

            foreach ($skus as $sku) {
                $outSkuSn = $sku['outSkuSn'] ?? null;
                $skuId = $sku['skuId'] ?? null;

                if (!$outSkuSn || !$skuId) {
                    Log::warning("Missing SKU data", $sku);
                    continue;
                }

                // ✅ Extract base price safely
                $price = null;

                if (isset($sku['priceInfo'])) {
                    $price = $sku['priceInfo']['salePrice'] 
                        ?? $sku['priceInfo']['price'] 
                        ?? null;
                }

                if (!$price && isset($sku['salePrice'])) {
                    $price = $sku['salePrice'];
                }

                // Ensure numeric
                $price = is_numeric($price) ? (float) $price : null;

                if ($price === null) {
                    Log::warning("Price missing for SKU {$outSkuSn}", $sku);
                    continue; // don’t overwrite with 0
                }

                TemuMetric::updateOrCreate(
                    ['sku' => $outSkuSn, 'sku_id' => $skuId],
                    ['base_price' => $price]
                );
            }

            $pageToken = $data['result']['pagination']['nextToken'] ?? null;
            $pageCount++;

            // Small delay to avoid API rate limits
            usleep(500000); // 0.5 sec

        } while ($pageToken);

        $this->info("SKUs Synced Successfully with Prices.");
    }

    
    private function generateSignValue($requestBody)
    {
        // Environment/config variables
        $appKey = env('TEMU_APP_KEY');
        $appSecret = env('TEMU_SECRET_KEY');
        $accessToken = env('TEMU_ACCESS_TOKEN');
        $timestamp = time();
        
        // Top-level params
        $params = [
            'access_token' => $accessToken,
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'data_type' => 'JSON',
        ];

        // Flatten and sort for signing
        $signParams = array_merge($params, $requestBody);
        ksort($signParams);
        
        $temp = '';
        foreach ($signParams as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            $temp .= $key . $value;
        }

        $signStr = $appSecret . $temp . $appSecret;
        $sign = strtoupper(md5($signStr));
        $params['sign'] = $sign;

        
        // Log the request
        $this->info("Generated Sign: $sign");
        return array_merge($params, $requestBody);
    }
}
