<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacebookAdsController extends Controller
{
    public function getAds()
    {
        $accessToken = env('FACEBOOK_ACCESS_TOKEN');
        $adAccountId = env('FACEBOOK_AD_ACCOUNT_ID');

        // dd($accessToken, $adAccountId);

        // Simple API call
        $response = Http::get("https://graph.facebook.com/v24.0/{$adAccountId}/ads", [
            'access_token' => $accessToken,
            'fields' => 'id,name,status,adset_id,campaign_id'
        ]);

        return $response->json();
    }

    public function getCampaigns()
    {
        $accessToken = env('FACEBOOK_ACCESS_TOKEN');
        $adAccountId = env('FACEBOOK_AD_ACCOUNT_ID');

        $response = Http::get("https://graph.facebook.com/v19.0/{$adAccountId}/campaigns", [
            'access_token' => $accessToken,
            'fields' => 'id,name,status,objective'
        ]);

        return $response->json();
    }

    public function getAdSets()
    {
        $accessToken = env('FACEBOOK_ACCESS_TOKEN');
        $adAccountId = env('FACEBOOK_AD_ACCOUNT_ID');

        $response = Http::get("https://graph.facebook.com/v19.0/{$adAccountId}/adsets", [
            'access_token' => $accessToken,
            'fields' => 'id,name,status,campaign_id,daily_budget'
        ]);

        return $response->json();
    }

    public function getInsights()
    {
        $accessToken = env('FACEBOOK_ACCESS_TOKEN');
        $adAccountId = env('FACEBOOK_AD_ACCOUNT_ID');

        $response = Http::get("https://graph.facebook.com/v19.0/{$adAccountId}/insights", [
            'access_token' => $accessToken,
            'fields' => 'impressions,clicks,spend,ctr,cpc,conversions',
            'date_preset' => 'last_30d'
        ]);

        return $response->json();
    }
}
