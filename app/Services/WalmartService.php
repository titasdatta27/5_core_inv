<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalmartService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected $marketplaceId;
    protected $token;

    public function __construct()
    {
        $this->clientId       = env('WALMART_CLIENT_ID');
        $this->clientSecret   = env('WALMART_CLIENT_SECRET');
        $this->baseUrl        = env('WALMART_API_ENDPOINT', 'https://marketplace.walmartapis.com');
        $this->marketplaceId  = env('WALMART_MARKETPLACE_ID', 'WMTMP');
        $this->token          = $this->getAccessToken();
    }


    public function getAccessToken()
    {
        $clientId     = env('WALMART_CLIENT_ID');
        $clientSecret = env('WALMART_CLIENT_SECRET');

        $authorization = base64_encode("{$clientId}:{$clientSecret}");

        $response = Http::asForm()->withHeaders([
            'Authorization'          => "Basic {$authorization}",
            'WM_QOS.CORRELATION_ID'  => uniqid(),
            'WM_SVC.NAME'            => 'Walmart Marketplace',
            'accept'                 => 'application/json',
        ])->post('https://marketplace.walmartapis.com/v3/token', [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'] ?? null;
        }

        // dd($response->json());

        return null;
    }

    public function updatePrice(string $sku, float $price): array
    {
        $accessToken = $this->getAccessToken();

        $payload = [
            'sku' => $sku,
            'pricing' => [
                [
                    'currentPriceType' => 'BASE',
                    'currentPrice' => [
                        'currency' => 'USD',
                        'amount' => number_format($price, 2, '.', '')
                    ]
                ]
            ]
        ];

        $endpoint = $this->baseUrl . "/v3/price";

        $response = Http::withHeaders([
            'WM_QOS.CORRELATION_ID' => uniqid(),
            'WM_SEC.ACCESS_TOKEN' => $accessToken,
            'WM_SVC.NAME' => 'Walmart Marketplace',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->put($endpoint, $payload);

        if ($response->failed()) {
            throw new Exception('Failed to update Walmart price: ' . $response->body());
        }
        Log::info('Walmart Price Update Response: ', $response->json());
        return $response->json();
    }

}
