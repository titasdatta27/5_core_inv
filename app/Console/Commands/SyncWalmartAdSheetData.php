<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\WalmartCampaignReport;

class SyncWalmartAdSheetData extends Command
{
    protected $signature = 'sync:walmart-ad-sheet-data';
    protected $description = 'Sync Walmart campaign performance from Google Apps Script API';

    public function handle()
    {
        $url = "https://script.google.com/macros/s/AKfycbxWwC98yCcPDcXjXfKpbE0dMC74L0YfF0fx2HdG_i3G7BzSjuhD8H9X98byGQymFNbx/exec";

        $response = Http::get($url);

        if (!$response->ok()) {
            $this->error('API Request Failed');
            return;
        }

        $json = $response->json();

        // Expected keys: L1, L7, L30
        foreach (['L1', 'L7', 'L30'] as $range) {

            if (!isset($json[$range]['data'])) {
                $this->warn("$range not found");
                continue;
            }

            foreach ($json[$range]['data'] as $row) {
                $campaignId = $this->idval($row['campaign_id'] ?? null);

                // If no campaign_id → skip
                // if (!$campaignId) {
                //     $this->warn("Skipping due to missing campaign_id → campaign: " . ($row['campaign_name'] ?? '-'));
                //     continue;
                // }

                WalmartCampaignReport::updateOrCreate(
                    [
                        'campaign_id'  => $campaignId,
                        'report_range' => $range,
                    ],
                    [
                        'campaignName'  => $row['campaign_name'] ?? null,
                        'budget'        => $this->num($row['daily_budget'] ?? null),
                        'spend'         => $this->num($row['ad_spend'] ?? null),
                        'cpc'           => $this->num($row['average_cpc'] ?? null),
                        'impressions'   => $this->num($row['impressions'] ?? null),
                        'clicks'        => $this->num($row['clicks'] ?? null),
                        'sold'          => $this->num($row['units_sold'] ?? null),
                        'status'        => $row['campaign_status'] ?? null,
                    ]
                );
            }

            $this->info("✅ Synced for $range");
        }

        $this->info("✅ All report ranges processed successfully.");
    }

    function num($v) {
        return ($v === "" || $v === null) ? null : $v;
    }

    function idval($v) {
        return ($v === "" || $v === null) ? null : $v;
    }
}
