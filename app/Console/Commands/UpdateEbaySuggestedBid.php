<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Models\EbayGeneralReport;

class UpdateEbaySuggestedBid extends Command
{
    protected $signature = 'ebay:update-suggestedbid';
    protected $description = 'Bulk update eBay ad bids using suggested_bid percentages';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle() {
        $this->info('Starting bulk eBay ad bid update...');

        $accessToken = $this->getEbayAccessToken();
        if (!$accessToken) {
            $this->error('Failed to obtain eBay access token.');
            return;
        }

        $campaignIds = EbayGeneralReport::distinct()->pluck('campaign_id');

        $campaignListings = DB::connection('apicentral')
            ->table('ebay_campaign_ads_listings')
            ->select('listing_id', 'campaign_id', 'suggested_bid')
            ->whereNotNull('campaign_id')
            ->whereNotNull('suggested_bid')
            ->whereIn('campaign_id', $campaignIds)
            ->get()
            ->groupBy('campaign_id');

        if ($campaignListings->isEmpty()) {
            $this->info('No campaign listings found.');
            return;
        }

        $client = new Client([
            'base_uri' => env('EBAY_BASE_URL', 'https://api.ebay.com/'),
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
        ]);

        foreach ($campaignListings as $campaignId => $listings) {
            $requests = [];

            foreach ($listings as $listing) {
                $requests[] = [
                    'listingId' => $listing->listing_id,
                    'bidPercentage' => (string) $listing->suggested_bid
                ];
            }

            if (empty($requests)) {
                continue;
            }

            try {
                $response = $client->post(
                    "sell/marketing/v1/ad_campaign/{$campaignId}/bulk_update_ads_bid_by_listing_id",
                    ['json' => ['requests' => $requests]]
                );

                $this->info("Campaign {$campaignId}: Updated " . count($requests) . " listings.");
                Log::info("eBay campaign {$campaignId} bulk update response: " . $response->getBody()->getContents());
            } catch (\Exception $e) {
                Log::error("Failed to update eBay campaign {$campaignId}: " . $e->getMessage());
                $this->error("Failed to update campaign {$campaignId}. Check logs.");
            }
        }

        $this->info('eBay ad bid update finished.');
    }

    private function getEbayAccessToken()
    {
        if (Cache::has('ebay_access_token')) {
            return Cache::get('ebay_access_token');
        }

        $clientId = env('EBAY_APP_ID');
        $clientSecret = env('EBAY_CERT_ID');
        $refreshToken = env('EBAY_REFRESH_TOKEN');
        $endpoint = "https://api.ebay.com/identity/v1/oauth2/token";

        $postFields = http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'scope' => 'https://api.ebay.com/oauth/api_scope/sell.marketing'
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic " . base64_encode("$clientId:$clientSecret")
            ],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 7200;

            Cache::put('ebay_access_token', $accessToken, $expiresIn - 60);

            return $accessToken;
        }

        throw new Exception("Failed to refresh token: " . json_encode($data));
    }
}
