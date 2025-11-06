<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\EbayMetric;
use App\Models\MarketplacePercentage;
use App\Models\ProductMaster;
use App\Services\EbayDataProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class FetchEbayReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ebay-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch eBay reports and store metrics in DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting FetchEbayReports (eBay1) command...');
        $token = $this->generateEbayToken();
        if (!$token) {
            $this->error('❌ Failed to generate eBay1 token.');
            return;
        }

        $this->info('✅ eBay1 token generated successfully.');
        
        // Validate this is the correct eBay account
        $accountInfo = Http::withToken($token)->get('https://api.ebay.com/sell/account/v1/seller');
        if ($accountInfo->ok()) {
            $username = $accountInfo->json()['username'] ?? 'Unknown';
            $this->info("🔍 eBay1 Account: {$username}");
            logger()->info('eBay1 account validated', ['username' => $username]);
        }

        $dateRanges = $this->getDateRanges();
        $listingData = $this->fetchAndParseReport('LMS_ACTIVE_INVENTORY_REPORT', null, $token);
        
        if (empty($listingData)) {
            $this->error('⚠️ No eBay1 listing data found!');
            return;
        }
        
        $this->info("📎 Found " . count($listingData) . " listings for eBay1 account");

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

            // 3. Store views in EbayMetric table for each SKU (eBay1 only)
            foreach ($viewsByItemId as $itemId => $views) {
                $sku = $itemIdToSku[$itemId] ?? null;
                if ($sku) {
                    // Only update records that belong to eBay1 account
                    $updated = EbayMetric::where('item_id', $itemId)
                        ->where('sku', $sku)
                        ->update(['views' => $views]);
                    if ($updated) {
                        logger()->info('eBay1: Updated views for item', ['item_id' => $itemId, 'sku' => $sku, 'views' => $views]);
                    }
                }
            }
        }

        $processedCount = 0;
        foreach ($listingData as $row) {
            $itemId = $row['item_id'] ?? null;
            if (!$itemId) continue;

            // ProductMaster se lp, ship values fetch karo
            $pm = ProductMaster::where('sku', $row['sku'])->first();

            // agar ProductMaster record nahi mila
            if (!$pm) {
                logger()->warning("ProductMaster not found for SKU: {$row['sku']}");
                $lp = 0;
                $ship = 0;
            } else {
                $values = is_array($pm->Values)
                    ? $pm->Values
                    : (is_string($pm->Values) ? json_decode($pm->Values, true) : []);

                $lp   = $values['lp']   ?? $pm->lp   ?? 0;
                $ship = $values['ship'] ?? $pm->ship ?? 0;
            }

            $percentage = MarketplacePercentage::where("marketplace", "EBay")->value("percentage") ?? 100;
            $percentage = $percentage / 100;

            // Ensure we only update eBay1 account data
            $metric = EbayMetric::updateOrCreate(
                [
                    'item_id' => $itemId,
                    'sku' => $row['sku'] ?? ''
                ],
                [
                    'ebay_price'  => $row['price'] ?? null,
                    'report_date' => now()->toDateString(),
                ]
            );
            
            logger()->info('eBay1: Processed item', [
                'item_id' => $itemId, 
                'sku' => $row['sku'] ?? '', 
                'price' => $row['price'] ?? null
            ]);

            try {
                $processor = new EbayDataProcessor();
                $processor->calculateAndSave($metric, $lp, $ship, $percentage);
            } catch (\Exception $e) {
                logger()->error("EbayDataProcessor failed for SKU: {$row['sku']}", [
                    'error' => $e->getMessage()
                ]);
                continue;
            }
            $processedCount++;
        }
        
        $this->info("📊 Processed {$processedCount} items for eBay1 account.");

       


        // 🔹 Orders se L30 & L60 quantities update karo
        $existingSkus = EbayMetric::pluck('sku')->filter()->toArray();
        $l30Qty = $this->getQuantityBySkuFromOrders($token, $dateRanges['l30']['start'], $dateRanges['l30']['end'], $existingSkus);
        $l60Qty = $this->getQuantityBySkuFromOrders($token, $dateRanges['l60']['start'], $dateRanges['l60']['end'], $existingSkus);

        foreach ($existingSkus as $sku) {
            $record = EbayMetric::where('sku', $sku)->first();
            if (!$record) continue;

            $record->ebay_l30 = $l30Qty[$sku] ?? 0;
            $record->ebay_l60 = $l60Qty[$sku] ?? 0;
            $record->save();
            
            logger()->info('eBay1: Updated quantities', [
                'sku' => $sku,
                'l30' => $l30Qty[$sku] ?? 0,
                'l60' => $l60Qty[$sku] ?? 0
            ]);
        }

        $this->info('✅ eBay1 metrics fetched and stored successfully.');
        $this->info("📈 Final eBay1 metrics: {$processedCount} items processed, " . count($l30Qty) . " L30 quantities, " . count($l60Qty) . " L60 quantities");
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
        
        $location = $response->header('Location');
        info('location', [$location]);
        if (!$location) {
            $this->error("No 'Location' header returned. Can't extract task ID.");
            logger()->error("Missing Location header", ['headers' => $response->headers()]);
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
        logger()->info($data);
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
                    logger()->info("XML Preview", json_decode(json_encode($xml), true));

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
                    logger()->info('Sample parsed items:', array_slice($data, 0, 5));
                    
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
                $this->info("Parsed " . count($data) . " TSV items.");
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
            logger()->info('response', [$response]);
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
        $clientId = env('EBAY_APP_ID');
        $clientSecret = env('EBAY_CERT_ID');

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
                    'refresh_token' => env('EBAY_REFRESH_TOKEN'),
                    'scope' => $scope,
                ]);

            if ($response->successful()) {
                Log::info('eBay1 token', ['response' => 'Token generated!']);
                $token = $response->json()['access_token'];
                
                $sellerResponse = Http::withToken($token)->get('https://api.ebay.com/sell/account/v1/seller');
                $sellerInfo = $sellerResponse->json();
                Log::info('eBay1 Seller info', $sellerInfo);
                
                // Validate seller account to ensure we have the right token
                if (isset($sellerInfo['username'])) {
                    Log::info('✅ eBay1 Account validated', ['username' => $sellerInfo['username']]);
                } else {
                    Log::warning('⚠️ eBay1 Account validation failed - no username found');
                }
                
                return $token;
            }

            Log::error('eBay token refresh error', ['response' => $response->json()]);
        } catch (\Exception $e) {
            Log::error('eBay token refresh exception: ' . $e->getMessage());
        }

        return null;
    }
}
