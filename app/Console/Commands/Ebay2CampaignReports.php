<?php

namespace App\Console\Commands;

use App\Models\Ebay2GeneralReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Ebay2CampaignReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ebay2-campaign-reports';

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
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            $this->error('Failed to retrieve eBay2 access token.');
            return;
        }

        // Step 1
        $ranges = [
            'L30' => [Carbon::today()->subDays(30), Carbon::today()->subDay()->endOfDay()],
            'L60' => [Carbon::today()->subDays(60), Carbon::today()->subDays(31)->endOfDay()],
        ];
        
        foreach ($ranges as $rangeKey => [$from, $to]) {
            $this->info("Processing CAMPAIGN_PERFORMANCE_REPORT: {$rangeKey} ({$from->toDateString()} → {$to->toDateString()})");

            $body = ["reportType" => "CAMPAIGN_PERFORMANCE_REPORT",
                "dateFrom" => $from,
                "dateTo" => $to,
                "marketplaceId" => "EBAY_US",
                "reportFormat" => "TSV_GZIP",
                "fundingModels" => ["COST_PER_SALE"],
                "dimensions" => [
                    ["dimensionKey" => "campaign_id"],
                    ["dimensionKey" => "listing_id"],
                ],
                "metricKeys" => [
                    "impressions",
                    "clicks",
                    "ad_fees",
                    "sales", 
                    "sale_amount",
                    "avg_cost_per_sale",
                    "ctr",
                ]
            ];

            $taskId = $this->submitReportTask($accessToken, $body);
            if (!$taskId) continue;

            $reportId = $this->pollReportStatus($accessToken, $taskId);
            if (!$reportId) continue;
    
            $items = $this->downloadParseAndStoreReport($accessToken, $reportId, $rangeKey);

            foreach($items as $item){
                if (!$item || empty($item['listing_id'])) continue;
                
                Ebay2GeneralReport::updateOrCreate(
                    ['listing_id' => $item['listing_id'], 'report_range' => $rangeKey],
                    [
                        'campaign_id' => $item['campaign_id'] ?? null,
                        'impressions' => $item['impressions'] ?? 0,
                        'clicks' => $item['clicks'] ?? 0,
                        'sales' => $item['sales'] ?? 0,
                        'ad_fees' => $item['ad_fees'] ?? null,
                        'sale_amount' => $item['sale_amount'] ?? null,
                        'avg_cost_per_sale' => $item['avg_cost_per_sale'] ?? null,
                        'ctr' => is_numeric($item['ctr'] ?? null) ? (float)$item['ctr'] : 0,
                        'channels' => $item['channels'] ?? null,
                    ]
                );
            }
            $this->info("CAMPAIGN_PERFORMANCE_REPORT Data stored for range: {$rangeKey}");
        }
        $this->info("✅ All campaign data processed.");
    }

    private function submitReportTask($token, $body)
    {
        $res = Http::withToken($token)->post('https://api.ebay.com/sell/marketing/v1/ad_report_task', $body);

        if ($res->failed()) {
            $this->error("Task creation failed: " . $res->body());
            return null;
        }
    
        // Extract task ID from Location header
        $location = $res->header('Location');
    
        if (!$location || !str_contains($location, '/ad_report_task/')) {
            $this->error("No Location header with task ID returned.");
            return null;
        }
    
        $taskId = basename($location);
    
        $this->info("✅ Report task submitted. Task ID: $taskId");
    
        return $taskId;
    }

    private function pollReportStatus($token, $taskId)
    {
        do {
            sleep(60);
            $check = Http::withToken($token)
                ->get("https://api.ebay.com/sell/marketing/v1/ad_report_task/{$taskId}");
            
            if ($check->status() === 401 || $check->json('errors.0.message') === 'Invalid access token') {
                logger()->error("Access token expired, refreshing...");

                $token = $this->getAccessToken();
                $check = Http::withToken($token)
                    ->get("https://api.ebay.com/sell/marketing/v1/ad_report_task/{$taskId}");
            }
            
            $status = $check['reportTaskStatus'] ?? 'IN_PROGRESS';

            logger()->info("Polling status for $taskId: $status");
            $this->info("Polling status for $taskId: $status");
        } while (!in_array($status, ['SUCCESS','FAILED']));

        if ($status !== 'SUCCESS') {
            logger()->error("Report task $taskId failed or timed out");
            $this->error("Report task $taskId failed or timed out");
            return [];
        }

        $reportId = $check['reportId'] ?? null;
        return $reportId;
    }

    private function downloadParseAndStoreReport($token, $reportId, $rangeKey)
    {
        $res = Http::withToken($token)->get("https://api.ebay.com/sell/marketing/v1/ad_report/{$reportId}");
        if (!$res->ok()) {
            $this->error("Failed to fetch report metadata.");
            return;
        }        

        $gzPath = storage_path("app/{$rangeKey}_{$reportId}.tsv.gz");
        file_put_contents($gzPath, $res->body());

        // Extract TSV
        $tsvPath = str_replace('.gz', '', $gzPath);
        $gz = gzopen($gzPath, 'rb');
        $tsv = fopen($tsvPath, 'wb');
        while (!gzeof($gz)) fwrite($tsv, gzread($gz, 4096));
        fclose($tsv); 
        gzclose($gz);

        $handle = fopen($tsvPath, 'rb');
        if (!$handle) {
            $this->error("Unable to open extracted TSV file.");
            return;
        }

        $headers = fgetcsv($handle, 0, "\t");
        if (!$headers) {
            $this->error("Header row missing or unreadable.");
            fclose($handle);
            return;
        }
        $allData = [];
        
        while (($line = fgetcsv($handle, 0, "\t")) !== false) {
            $item = array_combine($headers, $line);
            $allData[] = $item;
        }
        
        fclose($handle);

        @unlink($gzPath); 
        @unlink($tsvPath);
        
        return $allData;
    }

    private function getAccessToken()
    {
        $clientId = env('EBAY_2_APP_ID');
        $clientSecret = env('EBAY_2_CERT_ID');

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
            'https://api.ebay.com/oauth/api_scope/sell.marketing.readonly'
        ]);

        try {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post('https://api.ebay.com/identity/v1/oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => env('EBAY_2_REFRESH_TOKEN'),
                    'scope' => $scope,
                ]);

            if ($response->successful()) {
                Log::error('eBay2 token', ['response' => 'Token generated!']);
                return $response->json()['access_token'];
            }

            Log::error('eBay2 token refresh error', ['response' => $response->json()]);
        } catch (\Exception $e) {
            Log::error('eBay2 token refresh exception: ' . $e->getMessage());
        }

        return null;
    }
}
