<?php

namespace App\Services;

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\V20\Services\AdGroupOperation;
use Google\Ads\GoogleAds\V20\Services\AdGroupCriterionOperation;
use Google\Ads\GoogleAds\V20\Resources\AdGroup;
use Google\Ads\GoogleAds\V20\Resources\AdGroupCriterion;
use Google\Protobuf\FieldMask;
use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsStreamRequest;
use Google\Ads\GoogleAds\V20\Services\MutateAdGroupsRequest;
use Illuminate\Support\Facades\Log;
use Google\Ads\GoogleAds\V20\Services\MutateAdGroupCriteriaRequest;

class GoogleAdsSbidService
{
    protected $client;

    public function __construct()
    {
        $this->client = $this->buildClient();
    }

    private function buildClient()
    {
        $oAuth2Credential = new UserRefreshCredentials(
            ['https://www.googleapis.com/auth/adwords'],
            [
                'client_id'     => env('GOOGLE_ADS_CLIENT_ID'),
                'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
                'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
            ]
        );

        return (new GoogleAdsClientBuilder())
            ->withDeveloperToken(env('GOOGLE_ADS_DEVELOPER_TOKEN'))
            ->withLoginCustomerId(env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'))
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
    }

    /**
     * Run GAQL query
     */
    public function runQuery($customerId, $query)
    {
        $googleAdsService = $this->client->getGoogleAdsServiceClient();

        $request = new SearchGoogleAdsStreamRequest([
            'customer_id' => $customerId,
            'query' => $query,
        ]);

        $stream = $googleAdsService->searchStream($request);

        $results = [];
        foreach ($stream->iterateAllElements() as $row) {
            $results[] = json_decode($row->serializeToJsonString(), true);
        }

        return $results;
    }


    /**
     * Update Ad Group SBID
     */
    public function updateAdGroupSbid($customerId, $adGroupResourceName, $newSbid)
    {
        try {
            // Validate inputs
            if (empty($customerId) || empty($adGroupResourceName) || !is_numeric($newSbid)) {
                throw new \InvalidArgumentException("Invalid parameters for SBID update");
            }

            if ($newSbid <= 0) {
                throw new \InvalidArgumentException("SBID must be greater than 0, got: {$newSbid}");
            }

            $adGroupService = $this->client->getAdGroupServiceClient();

            $bidMicros = round($newSbid * 1_000_000);
            $billableUnit = 10000; // $0.01 in micros
            $bidMicros = round($bidMicros / $billableUnit) * $billableUnit;
            
            // Ensure minimum bid (usually $0.01)
            if ($bidMicros < $billableUnit) {
                $bidMicros = $billableUnit;
            }
            
            Log::info("Updating AdGroup SBID", [
                'customer_id' => $customerId,
                'ad_group' => $adGroupResourceName,
                'new_sbid' => $newSbid,
                'bid_micros' => $bidMicros,
                'bid_rounded' => $bidMicros / 1_000_000
            ]);

            $adGroup = new AdGroup([
                'resource_name' => $adGroupResourceName,
                'cpc_bid_micros' => $bidMicros
            ]);

            $operation = new AdGroupOperation();
            $operation->setUpdate($adGroup);
            $operation->setUpdateMask(new FieldMask(['paths' => ['cpc_bid_micros']]));

            $request = new MutateAdGroupsRequest([
                'customer_id' => $customerId,
                'operations' => [$operation]
            ]);

            $response = $adGroupService->mutateAdGroups($request);
            
            // Validate response
            if (!$response || !$response->getResults()) {
                throw new \Exception("No response received from Google Ads API");
            }

            $results = $response->getResults();
            if (count($results) === 0) {
                throw new \Exception("No results returned from ad group update operation");
            }

            Log::info("AdGroup SBID updated successfully", [
                'customer_id' => $customerId,
                'ad_group' => $adGroupResourceName,
                'new_sbid' => $newSbid,
                'final_bid_micros' => $bidMicros,
                'final_bid_dollars' => $bidMicros / 1_000_000,
                'response_count' => count($results)
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error("Failed to update AdGroup SBID", [
                'customer_id' => $customerId,
                'ad_group' => $adGroupResourceName,
                'new_sbid' => $newSbid,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    public function updateProductGroupSbid($customerId, $productGroupResourceName, $newSbid)
    {
        try {
            // Validate inputs
            if (empty($customerId) || empty($productGroupResourceName) || !is_numeric($newSbid)) {
                throw new \InvalidArgumentException("Invalid parameters for product group SBID update");
            }

            if ($newSbid <= 0) {
                throw new \InvalidArgumentException("SBID must be greater than 0, got: {$newSbid}");
            }

            $adGroupCriterionService = $this->client->getAdGroupCriterionServiceClient();

            $bidMicros = round($newSbid * 1_000_000);
            $billableUnit = 10000; // $0.01 in micros
            $bidMicros = round($bidMicros / $billableUnit) * $billableUnit;
            
            // Ensure minimum bid (usually $0.01)
            if ($bidMicros < $billableUnit) {
                $bidMicros = $billableUnit;
            }
            
            Log::info("Updating Product Group SBID", [
                'customer_id' => $customerId,
                'product_group' => $productGroupResourceName,
                'new_sbid' => $newSbid,
                'bid_micros' => $bidMicros,
                'bid_rounded' => $bidMicros / 1_000_000
            ]);

            $criterion = new AdGroupCriterion([
                'resource_name' => $productGroupResourceName,
                'cpc_bid_micros' => $bidMicros
            ]);

            $operation = new AdGroupCriterionOperation();
            $operation->setUpdate($criterion);
            $operation->setUpdateMask(new FieldMask(['paths' => ['cpc_bid_micros']]));

            // Wrap operation in a proper request object
            $request = new MutateAdGroupCriteriaRequest([
                'customer_id' => $customerId,
                'operations' => [$operation]
            ]);

            $response = $adGroupCriterionService->mutateAdGroupCriteria($request);

            // Validate response
            if (!$response || !$response->getResults()) {
                throw new \Exception("No response received from Google Ads API for product group update");
            }

            $results = $response->getResults();
            if (count($results) === 0) {
                throw new \Exception("No results returned from product group update operation");
            }

            Log::info("Product Group SBID updated successfully", [
                'customer_id' => $customerId,
                'product_group' => $productGroupResourceName,
                'new_sbid' => $newSbid,
                'final_bid_micros' => $bidMicros,
                'final_bid_dollars' => $bidMicros / 1_000_000,
                'response_count' => count($results)
            ]);

            return $response;
            
        } catch (\Exception $e) {
            Log::error("Failed to update Product Group SBID", [
                'customer_id' => $customerId,
                'product_group' => $productGroupResourceName,
                'new_sbid' => $newSbid,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    public function updateCampaignSbids($customerId, $campaignId, $sbidFactor)
    {
        try {
            Log::info("Starting SBID update for campaign", [
                'customer_id' => $customerId,
                'campaign_id' => $campaignId,
                'sbid_factor' => $sbidFactor
            ]);

            $adGroupQuery = "
                SELECT ad_group.resource_name, metrics.clicks, metrics.cost_micros
                FROM ad_group
                WHERE ad_group.campaign = 'customers/{$customerId}/campaigns/{$campaignId}'
            ";

            $adGroups = $this->runQuery($customerId, $adGroupQuery);
            
            Log::info("Found ad groups for campaign", [
                'campaign_id' => $campaignId,
                'ad_groups_count' => count($adGroups),
                'ad_groups_data' => $adGroups
            ]);

            if (empty($adGroups)) {
                Log::warning("No ad groups found for campaign", ['campaign_id' => $campaignId]);
                throw new \Exception("No ad groups found for campaign ID: {$campaignId}");
            }

            $processedAdGroups = 0;
            $processedProductGroups = 0;

            foreach ($adGroups as $row) {
                // Fix: Use correct field names from Google Ads API response
                $adGroup = $row['adGroup'] ?? [];
                $metrics = $row['metrics'] ?? [];
                $adGroupResource = $adGroup['resourceName'] ?? null;

                if ($adGroupResource) {
                    try {
                        $this->updateAdGroupSbid($customerId, $adGroupResource, $sbidFactor);
                        $processedAdGroups++;
                    } catch (\Exception $e) {
                        Log::error("Failed to update ad group SBID", [
                            'ad_group_resource' => $adGroupResource,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with other ad groups
                    }

                    // Query product groups for this ad group
                    $productGroupQuery = "
                        SELECT ad_group_criterion.resource_name, 
                               ad_group_criterion.listing_group.type,
                               ad_group_criterion.negative
                        FROM ad_group_criterion
                        WHERE ad_group_criterion.ad_group = '{$adGroupResource}'
                          AND ad_group_criterion.listing_group.type = 'UNIT'
                          AND ad_group_criterion.negative = FALSE
                    ";

                    try {
                        $productGroups = $this->runQuery($customerId, $productGroupQuery);
                        
                        foreach ($productGroups as $pgRow) {
                            $pgResource = $pgRow['adGroupCriterion']['resourceName'] ?? null;
                            if ($pgResource) {
                                try {
                                    $this->updateProductGroupSbid($customerId, $pgResource, $sbidFactor);
                                    $processedProductGroups++;
                                } catch (\Exception $e) {
                                    Log::error("Failed to update product group SBID", [
                                        'product_group_resource' => $pgResource,
                                        'error' => $e->getMessage()
                                    ]);
                                    // Continue with other product groups
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to query product groups", [
                            'ad_group_resource' => $adGroupResource,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::warning("No resource name found for ad group", ['row' => $row]);
                }
            }

            Log::info("Completed SBID update for campaign", [
                'campaign_id' => $campaignId,
                'processed_ad_groups' => $processedAdGroups,
                'processed_product_groups' => $processedProductGroups
            ]);

            // If no ad groups were processed, throw an exception
            if ($processedAdGroups === 0) {
                throw new \Exception("Failed to update any ad groups for campaign ID: {$campaignId}");
            }

        } catch (\Exception $e) {
            Log::error("Failed to update campaign SBIDs", [
                'customer_id' => $customerId,
                'campaign_id' => $campaignId,
                'sbid_factor' => $sbidFactor,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

}
