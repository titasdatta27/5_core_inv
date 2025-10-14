<?php

namespace App\Console\Commands;

use App\Models\Ebay3Metric;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class FetchEbay3Metrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ebay-three-metrics';

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
        $token = $this->generateEbayToken();

        if (!$token) {
            $this->error('Failed to generate token.');
            return;
        }

        $dateRanges = $this->getDateRanges();

        $listingData = $this->fetchAndParseReport('LMS_ACTIVE_INVENTORY_REPORT', null, $token);

         // 1. Gather all item_ids from listingData
        $itemIdToSku = [];
        foreach ($listingData as $row) {
            if (!empty($row['item_id']) && !empty($row['sku'])) {
                $itemIdToSku[$row['item_id']] = $row['sku'];
            }
        }

        // 2. Fetch views from eBay Analytics API for all item_ids
        if (!empty($itemIdToSku)) {
            $itemIds = array_keys($itemIdToSku);
            $itemIdChunks = array_chunk($itemIds, 20); // eBay API may have limits
            $viewsByItemId = [];
            foreach ($itemIdChunks as $chunk) {
                $ids = implode('|', $chunk);
                $dateRange = now()->subDays(30)->format('Ymd') . '..' . now()->format('Ymd');
                $url = "https://api.ebay.com/sell/analytics/v1/traffic_report?dimension=LISTING&filter=listing_ids:%7B{$ids}%7D,date_range:[{$dateRange}]&metric=LISTING_VIEWS_TOTAL&sort=LISTING_VIEWS_TOTAL";
                $response = Http::withToken($token)->get($url);
                if ($response->ok()) {
                    $data = $response->json();
                    foreach ($data['records'] ?? [] as $record) {
                        $itemId = $record['dimensionValues'][0]['value'] ?? null;
                        $views = $record['metricValues'][0]['value'] ?? null;
                        if ($itemId && $views !== null) {
                            $viewsByItemId[$itemId] = $views;
                        }
                    }
                }
            }

        }

        foreach ($listingData as $row) {
            $itemId = $row['item_id'] ?? null;
            if (!$itemId) continue;
        
            Ebay3Metric::updateOrCreate(
                ['item_id' => $itemId, 'sku' => $row['sku'] ?? ''],
                [
                    'ebay_price' => $row['price'] ?? null,
                    'report_range' => now()->toDateString(),
                ]
            );
        }

        // 3. Store views in Ebay3Metric table for each item_id
        if (!empty($viewsByItemId)) {
            foreach ($viewsByItemId as $itemId => $views) {
                Ebay3Metric::where('item_id', $itemId)->update(['views' => $views]);
            }
        }

        $existingSkus = Ebay3Metric::pluck('sku')->filter()->toArray();
        $l30Qty = $this->getQuantityBySkuFromOrders($token, $dateRanges['l30']['start'], $dateRanges['l30']['end'], $existingSkus);
        $l60Qty = $this->getQuantityBySkuFromOrders($token, $dateRanges['l60']['start'], $dateRanges['l60']['end'], $existingSkus);
        
        foreach ($existingSkus as $sku) {
            $record = Ebay3Metric::where('sku', $sku)->first();
            if (!$record) continue;
            
            $record->ebay_l30 = $l30Qty[$sku] ?? 0;
            $record->ebay_l60 = $l60Qty[$sku] ?? 0;
            $record->save();
        }
        $this->info('eBay3 metrics fetched and stored successfully.');
    }

    private function getDateRanges(): array
    {
        $today = Carbon::today();
        return [
            'l30' => [
                'start' => $today->copy()->subDays(30)->addDay(),
                'end' => $today->copy()->subDay(),
            ],
            'l60' => [
                'start' => $today->copy()->subDays(60)->addDay(),
                'end' => $today->copy()->subDays(31),
            ],
        ];
    }

    private function fetchAndParseReport($reportType, $range, $token): array
    {
        $this->info("Start Processing: $reportType");

        $apiUrl = 'https://api.ebay.com/sell/feed/v1/inventory_task';

        $payload = [
            'feedType' => $reportType,
            'format' => 'TSV_GZIP',
            'schemaVersion' => '1.0',
        ];

        info('Request Payload:', [$payload]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, $payload);
        
        if (!$response->successful()) {
            $this->error('Failed to create inventory task: ' . $response->status() . ' ' . $response->body());
            return [];
        }
        
        $location = $response->header('Location');
        info('location', [$location]);
        if (!$location) {
            $this->error("No 'Location' header returned. Can't extract task ID.");
            logger()->error("Missing Location header", ['headers' => $response->headers()]);
            logger()->error("Response", [$response]);
            return [];
        }

        // Step 2: Extract the task ID from URL
        $taskId = basename($location); 
        $this->info("Task ID: $taskId");

        $this->info("Task/Report ID: $taskId");

        $status = null;
        $downloadUrl = null;

        do {
            sleep(10);
        
            $statusResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get("https://api.ebay.com/sell/feed/v1/inventory_task/{$taskId}");
        
            $status = $statusResponse['status'] ?? 'PENDING';
            $this->info("Status: $status");
        
        } while (!in_array($status, ['COMPLETED', 'COMPLETED_WITH_ERROR', 'FAILED']));
        
        if ($status === 'FAILED') {
            logger()->error("Inventory report task failed.");
            return [];
        }

        $data = $this->downloadAndParseEbayReport($taskId, $token);
        
        return $data;
    }

    private function downloadAndParseEbayReport(string $taskId, string $token): array
    {   
        info('downloadAndParseEbayReport');
        $baseTaskUrl = "https://api.ebay.com/sell/feed/v1/task/{$taskId}/download_result_file";
        $filePath = storage_path("app/inventory_{$taskId}");
        $zipPath = $filePath . ".zip";
        $xmlPath = $filePath . ".xml";

        $this->info("Downloading report from: $baseTaskUrl");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($baseTaskUrl);

            $content = $response->body();
            $magic = substr($content, 0, 2);

            // ZIP file: starts with "PK"
            if ($magic === "PK") {
                file_put_contents($zipPath, $content);

                $zip = new ZipArchive;
                if ($zip->open($zipPath) === TRUE) {
                    $zip->extractTo(storage_path('app/'));
                    $zip->close();

                    // Find extracted XML file
                    $extractedFiles = glob(storage_path('app/*.xml'));
                    if (empty($extractedFiles)) {
                        logger()->error("No XML file found in zip.");
                        return [];
                    }

                    $xmlPath = $extractedFiles[0];
                    $xml = simplexml_load_file($xmlPath);
                    if (!$xml) {
                        logger()->error("Failed to parse XML.");
                        return [];
                    }

                    logger()->info("Root Element: " . $xml->getName());                    

                    // Example conversion (customize based on XML structure)
                    $data = [];
                    foreach ($xml->ActiveInventoryReport->SKUDetails as $item) {
                        $itemId = (string) $item->ItemID ?? null;
                        if (!$itemId) continue;
                    
                        $data[] = [
                            'item_id' => $itemId,
                            'sku' => $item->SKU ?? '',
                            'price' => (float) ($item->Price ?? 0),
                        ];
                    
                        // Handle variations if any
                        if (!empty($item->Variations->Variation)) {
                            foreach ($item->Variations->Variation as $variation) {
                                $itemId = (string) $item->ItemID ?? null;
                                $data[] = [
                                    'item_id' => $itemId,
                                    'sku' => $variation->SKU ?? '',
                                    'price' => (float) ($variation->Price ?? 0),
                                ];
                            }
                        }
                    }

                    @unlink($zipPath);
                    @unlink($xmlPath);
                    
                    $this->info("Parsed " . count($data) . " XML items.");
                    
                    return $data;
                } else {
                    logger()->error("Failed to open ZIP file.");
                    return [];
                }
            }

            // If not ZIP, check for GZ
            if (substr($content, 0, 2) === "\x1f\x8b") {
                $gzPath = $filePath . ".tsv.gz";
                $tsvPath = $filePath . ".tsv";
                file_put_contents($gzPath, $content);

                $gz = gzopen($gzPath, 'rb');
                $tsv = fopen($tsvPath, 'wb');
                while (!gzeof($gz)) {
                    fwrite($tsv, gzread($gz, 4096));
                }
                fclose($tsv);
                gzclose($gz);

                $lines = file($tsvPath, FILE_SKIP_EMPTY_LINES);
                if (!$lines || count($lines) < 2) return [];

                $rows = array_map('str_getcsv', $lines, array_fill(0, count($lines), "\t"));
                $headers = array_shift($rows);
                $data = [];

                foreach ($rows as $row) {
                    if (count($headers) !== count($row)) continue;
                    $item = array_combine($headers, $row);
                    $itemId = $item['itemId'] ?? null;
                    if (!$itemId) continue;

                    $data[$itemId] = [
                        'price' => $item['price'] ?? null,
                        'sku' => $item['sku'] ?? null,
                    ];
                }

                @unlink($gzPath);
                @unlink($tsvPath);
                return $data;
            }

            // Unknown content
            logger()->error("Unknown file type", [
                'first_bytes' => bin2hex(substr($content, 0, 4)),
                'taskId' => $taskId,
            ]);
            return [];

        } catch (\Throwable $e) {
            logger()->error("Exception: " . $e->getMessage());
            return [];
        }
    }

    private function getQuantityBySkuFromOrders($token, Carbon $from, Carbon $to, array $onlyTheseSkus = [])
    {
        $allQuantities = [];

        $url = "https://api.ebay.com/sell/fulfillment/v1/order?filter=creationdate:[{$from->format('Y-m-d\TH:i:s.000\Z')}..{$to->format('Y-m-d\TH:i:s.000\Z')}]&limit=200";
    
        do {
            $response = Http::withToken($token)->get($url);
            if (!$response->ok()) {
                logger()->error("Fulfillment fetch failed: " . $response->body());
                break;
            }
    
            $data = $response->json();
            foreach ($data['orders'] ?? [] as $order) {
                
                foreach ($order['lineItems'] ?? [] as $line) {
                    
                    $sku = $line['sku'] ?? null;
                    $qty = (int) ($line['quantity'] ?? 0);
                    if (!$sku || !in_array($sku, $onlyTheseSkus)) continue;
    
                    $allQuantities[$sku] = ($allQuantities[$sku] ?? 0) + $qty;
                }
            }
    
            $url = $data['next'] ?? null;
    
        } while ($url);
    
        return $allQuantities;
    }

    private function generateEbayToken(): ?string
    {
        $clientId = env('EBAY_3_APP_ID');
        $clientSecret = env('EBAY_3_CERT_ID');

        $scope = implode(' ', [
            'https://api.ebay.com/oauth/api_scope',
            'https://api.ebay.com/oauth/api_scope/sell.account',
            'https://api.ebay.com/oauth/api_scope/sell.inventory',
            'https://api.ebay.com/oauth/api_scope/sell.account',
            'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
            'https://api.ebay.com/oauth/api_scope/sell.analytics.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.stores',
            'https://api.ebay.com/oauth/api_scope/sell.finances',
            'https://api.ebay.com/oauth/api_scope/sell.marketing',
        ]);

        try {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post('https://api.ebay.com/identity/v1/oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => env('EBAY_3_REFRESH_TOKEN'),
                    'scope' => $scope,
                ]);

            if ($response->successful()) {
                Log::error('eBay3 token', ['response' => 'Token generated!']);
                return $response->json()['access_token'];
            }

            Log::error('eBay3 token refresh error', ['response' => $response->json()]);
        } catch (\Exception $e) {
            Log::error('eBay3 token refresh exception: ' . $e->getMessage());
        }

        return null;
    }
}
