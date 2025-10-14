<?php

namespace App\Console\Commands;

use App\Models\WaifairProductSheet;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;
use App\Models\WayfairDataView;
use App\Models\WayfairProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncWayfairSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:wayfair-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Wayfair Product Sheet'; 

    /**
     * Execute the console command.
    */
  

    // protected $apiUrl;
    // protected $apiKey;
    // protected $password;

    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->apiUrl    = "https://" . env('SHOPIFY_5CORE_DOMAIN') . "/admin/api/2024-10";
    //     $this->apiKey    = env('SHOPIFY_5CORE_API_KEY');
    //     $this->password  = env('SHOPIFY_5CORE_PASSWORD');
    // }

    // public function handle()
    // {
    //     $this->info("Fetching Wayfair data from Shopify…");

    //     $now = Carbon::now();

    //     $l30Start = $now->copy()->subDays(30);
    //     $l30End   = $now->copy()->subDay(); // yesterday

    //     // L60 = 30 days before that
    //     $l60Start = $now->copy()->subDays(60);
    //     $l60End   = $now->copy()->subDays(31);

    //     $this->info("L30 window: {$l30Start->toDateString()} → {$l30End->toDateString()}");
    //     $this->info("L60 window: {$l60Start->toDateString()} → {$l60End->toDateString()}");

    //     $endpoint = "{$this->apiUrl}/orders.json?status=any&limit=250"
    //         . "&created_at_min={$l60Start->toIso8601String()}"
    //         . "&created_at_max={$l30End->toIso8601String()}";

    //     $groupedData = [];

    //     while ($endpoint) {
    //         $response = Http::withBasicAuth($this->apiKey, $this->password)->get($endpoint);

    //         if (!$response->successful()) {
    //             $this->error("Failed to fetch Shopify orders");
    //             $this->error("Status: " . $response->status());
    //             $this->error("Body: " . $response->body());
    //             return; 
    //         }

    //         $orders = $response->json('orders') ?? [];
    //         $this->info("Fetched " . count($orders) . " orders…");

    //         // filter Wayfair orders
    //         $wayfairOrders = collect($orders)->filter(function ($order) {
    //             $tags = strtolower($order['tags'] ?? '');
    //             if (str_contains($tags, 'wayfair')) {
    //                 return true;
    //             }

    //             if (!empty($order['note_attributes'])) {
    //                 foreach ($order['note_attributes'] as $attr) {
    //                     if (
    //                         strtolower($attr['name'] ?? '') === 'channel' &&
    //                         strtolower($attr['value'] ?? '') === 'wayfair'
    //                     ) {
    //                         return true;
    //                     }
    //                 }
    //             }

    //             $source = strtolower($order['source_name'] ?? '');
    //             return str_contains($source, 'wayfair');
    //         });

    //         // group SKUs
    //         foreach ($wayfairOrders as $order) {
    //             $date = Carbon::parse($order['created_at']);

    //             foreach ($order['line_items'] as $item) {
    //                 $sku = $item['sku'];
    //                 $qty = (int) $item['quantity'];
    //                 $price = $item['price'];

    //                 if (!isset($groupedData[$sku])) {
    //                     $groupedData[$sku] = [
    //                         'l30' => 0,
    //                         'l60' => 0,
    //                         'price' => $price,
    //                     ];
    //                 }

    //                 if ($date->between($l30Start, $l30End)) {
    //                     $groupedData[$sku]['l30'] += $qty;
    //                 }

    //                 if ($date->between($l60Start, $l60End)) {
    //                     $groupedData[$sku]['l60'] += $qty;
    //                 }

    //                 // update latest price
    //                 $groupedData[$sku]['price'] = $price;
    //             }
    //         }

    //         // get next page from Link header
    //         $linkHeader = $response->header('Link');
    //         if ($linkHeader && preg_match('/<([^>]+)>; rel="next"/', $linkHeader, $matches)) {
    //             $endpoint = $matches[1];
    //         } else {
    //             $endpoint = null;
    //         }
    //     }

    //     $this->info("Updating database…");

    //     foreach ($groupedData as $sku => $values) {
    //         WaifairProductSheet::updateOrCreate(
    //             ['sku' => $sku],
    //             [
    //                 'l30'   => $values['l30'],
    //                 'l60'   => $values['l60'],
    //                 'price' => $values['price'],
    //             ]
    //         );
    //     }

    //     $this->info("Wayfair L30/L60/Price updated successfully (" . count($groupedData) . " SKUs)");
    // }    

    public function handle()
    {
        $url = 'https://script.google.com/macros/s/AKfycbxkkmo4L0EbqNK6WaOqM73yUuvC4mwAJMDJcfebxNnzwZ_LuL_9SIOtP09moPFHjV27/exec';

        $this->info('Starting wayfair sheet sync...');

        try {
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error('Failed to fetch data from Google Sheet.');
                return;
            }

            $payload = $response->json();

            //  Auto-detect structure (either {data: [...]} or [...])
            $data = isset($payload['data']) ? $payload['data'] : $payload;

            if (!is_array($data) || empty($data)) {
                $this->warn('No valid data found.');
                return;
            }

            $savedCount = 0;

            foreach ($data as $item) {
                $sku = $item['sku'] ?? null;
                if (empty($sku)) {
                    $this->warn("Skipped a row with missing SKU.");
                    continue;
                }

                WaifairProductSheet::updateOrCreate(
                    ['sku' => trim($sku)],
                    [
                        'l30'  => $item['l30'] ?? null,
                        'l60'  => $item['l60'] ?? null,
                        'price'  => isset($item['price']) && $item['price'] !== '' ? $item['price'] : null,
                        'views'  => isset($item['views']) && $item['views'] !== '' ? (int)$item['views'] : null,
                    ]
                );

                $savedCount++;
            }

            $this->info("wayfair Sheet data synced successfully! Total records saved/updated: {$savedCount}");
        } catch (\Exception $e) {
            Log::error('Error syncing wayfair sheet: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
        }
    }

}
