<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use App\Models\TiktokSheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TiktokSheetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'sync:tiktok-sheet-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TikTok product sheet data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://script.google.com/macros/s/AKfycbyj1Z0xGDKHOWZvqj1fdnBi02abq67NzwBc7fj0XckA9O3zGbZOyHnLLDXuOPnTLC3E/exec';

        try {
            $response = Http::timeout(seconds: 120)->get($url);
            if ($response->successful()) {
                $data = $response->json();
                $this->info('Fetched data: ' . json_encode($data));
                $rows = collect($data['data'] ?? $data ?? []);
                $this->info('Rows count: ' . $rows->count());
            } else {
                $this->error('Failed to fetch data from Google Sheet. Status: ' . $response->status());
                return;
            }
        } catch (\Exception $e) {
            $this->error('Exception while fetching data: ' . $e->getMessage());
            return;
        }

        foreach ($rows as $row) {
            $sku = trim($row['SKU'] ?? '');
            if (!$sku) continue;

            TiktokSheet::updateOrCreate(
                ['sku' => $sku],
                [
                    'price'     => $this->toDecimalOrNull($row['live price '] ?? null),
                    'l30'       => $this->toIntOrNull($row['TL30'] ?? null),
                    'l60'       => $this->toIntOrNull($row['TL60'] ?? null),
                    'views'       => $this->toDecimalOrNull($row['P Views'] ?? null),
                   
                ]
            );
        }

        $this->info('tiktok sheet data synced successfully!');

        // Fetch L30 and L60 from Shopify for TikTok SKUs
        $this->fetchShopifyTiktokData();
    }

    private function fetchShopifyTiktokData()
    {
        $this->info('Fetching Shopify data for TikTok L30/L60...');

        $now = Carbon::now();
        $sixtyDaysAgo = $now->copy()->subDays(60);
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $baseUrl = "https://" . env('SHOPIFY_STORE_URL') . "/admin/api/2024-10/orders.json";
        $params = [
            'status' => 'any',
            'created_at_min' => $sixtyDaysAgo->toISOString(),
            'created_at_max' => $now->toISOString(),
            'limit' => 250 
        ];

        $allOrders = [];
        $page = 1;

        do {
            $url = $baseUrl . '?' . http_build_query($params);

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN')
            ])->get($url);

            if ($response->failed()) {
                $this->error('Failed to fetch Shopify orders: ' . $response->body());
                return;
            }

            $data = $response->json();
            $orders = $data['orders'] ?? [];
            $allOrders = array_merge($allOrders, $orders);

            $this->info("Fetched page {$page}: " . count($orders) . " orders");

            // Check for next page
            $linkHeader = $response->header('Link');
            $nextUrl = $this->getNextPageUrl($linkHeader);

            if ($nextUrl) {
                // Extract query params from next URL
                $parsedUrl = parse_url($nextUrl);
                parse_str($parsedUrl['query'], $params);
                $page++;
            } else {
                break;
            }
        } while (true);

        $this->info('Total orders fetched: ' . count($allOrders));

        $skuCountsL60 = [];
        $skuCountsL30 = [];
        $skuPrices = [];
        $totalItems = 0;
        $tiktokItems = 0;
        $tiktokOrders = 0;
        $totalOrders = count($allOrders);

        foreach ($allOrders as $order) {
            $createdAt = Carbon::parse($order['created_at']);
            $sourceName = $order['source_name'] ?? null;
            
            // Only process TikTok orders
            if (strtolower($sourceName) === 'tiktok') {
                $tiktokOrders++;
                
                foreach ($order['line_items'] as $item) {
                    $sku = $item['sku'] ?? null;
                    $totalItems++;
                    
                    if ($sku) {
                        $tiktokItems++;
                        $quantity = $item['current_quantity'] ?? 0;
                        $price = $item['price'] ?? 0;

                        if ($createdAt >= $sixtyDaysAgo) {
                            $skuCountsL60[$sku] = ($skuCountsL60[$sku] ?? 0) + $quantity;
                        }

                        if ($createdAt >= $thirtyDaysAgo) {
                            $skuCountsL30[$sku] = ($skuCountsL30[$sku] ?? 0) + $quantity;
                        }

                        // Track the latest price for each SKU
                        if (!isset($skuPrices[$sku]) || $createdAt > $skuPrices[$sku]['date']) {
                            $skuPrices[$sku] = [
                                'price' => $price,
                                'date' => $createdAt
                            ];
                        }
                    }
                }
            }
        }

        $this->info("Total orders processed: {$totalOrders}");
        $this->info("TikTok orders found: {$tiktokOrders}");
        $this->info("Non-TikTok orders filtered: " . ($totalOrders - $tiktokOrders));
        $this->info("Total line items in TikTok orders: {$totalItems}");
        $this->info("TikTok items with SKUs: {$tiktokItems}");

        // Update TiktokSheet with the counts and prices - for SKUs that have prices
        foreach ($skuPrices as $sku => $priceData) {
            $updateData = [
                'shopify_tiktok_price' => $priceData['price'],
            ];

            // Only add L30/L60 if they exist for this SKU
            if (isset($skuCountsL60[$sku])) {
                $updateData['shopify_tiktokl60'] = $skuCountsL60[$sku];
                $updateData['shopify_tiktokl30'] = $skuCountsL30[$sku] ?? 0;
            }

            TiktokSheet::where('sku', $sku)->update($updateData);
        }

        // Update L30/L60 for all SKUs that have sales data but no prices
        foreach ($skuCountsL60 as $sku => $l60) {
            if (!isset($skuPrices[$sku])) {
                $updateData = [
                    'shopify_tiktokl60' => $l60,
                    'shopify_tiktokl30' => $skuCountsL30[$sku] ?? 0,
                ];
                TiktokSheet::where('sku', $sku)->update($updateData);
            }
        }

        $this->info('Shopify TikTok data updated successfully!');
    }

    private function getNextPageUrl($linkHeader)
    {
        if (!$linkHeader) {
            return null;
        }

        // Link header format: <https://...>; rel="next", <https://...>; rel="previous"
        $links = explode(',', $linkHeader);
        foreach ($links as $link) {
            if (strpos($link, 'rel="next"') !== false) {
                preg_match('/<([^>]+)>/', $link, $matches);
                return $matches[1] ?? null;
            }
        }

        return null;
    }

    private function toDecimalOrNull($value)
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) return null;
        return (string)$value; // Keep as string to preserve exact decimal representation
    }

    private function toIntOrNull($value)
    {
        if ($value === null || $value === '') return null;
        $value = str_replace(',', '', $value);
        return is_numeric($value) ? (int)$value : null;
    }
}
