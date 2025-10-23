<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Aws\Signature\SignatureV4;
use Aws\Credentials\Credentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ProductStockMapping;
class AliExpressApiService
{
  public function generateAliExpressSignature(array $params, string $apiSecret): string
{
    ksort($params);
    $sortedString = '';
    foreach ($params as $key => $value) {
        $sortedString .= $key . $value;
    }

    $signBase = $apiSecret . $sortedString . $apiSecret;
    return strtoupper(md5($signBase));
}

public function getInventory()
{
    $timestamp = date('Y-m-d H:i:s');

    $params = [
        'method' => 'aliexpress.ds.product.get',
        'app_key' => env('ALIEXPRESS_APP_KEY'),
        'sign_method' => 'md5',
        'timestamp' => $timestamp,
        'format' => 'json',
        'v' => '2.0',
        // 'product_ids' => $productIds, // comma-separated string of product IDs
        'target_currency' => 'USD',
        'target_language' => 'EN',
    ];

    $apiSecret=env('ALIEXPRESS_APP_SECRET');
    $params['sign'] = $this->generateAliExpressSignature($params, $apiSecret);

    $response = Http::withoutVerifying()
        ->asForm()
        ->post('https://api.aliexpress.com/aliexpress.ds.product.get', $params);

    if ($response->successful()) {
        return $response->json();
    } else {
        logger()->error('AliExpress API error: ' . $response->body());
        return null;
    }
}

}
