<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateEbaySPriceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UpdatePriceApiController extends Controller
{
    //update price in shopify by variant id
    public static function updateShopifyVariantPrice($variantId, $newPrice)
    {
        try {
            $storeUrl = "https://" . env('SHOPIFY_STORE_URL');
            $apiVersion = "2025-01";
            $accessToken = env('SHOPIFY_PASSWORD');

            $url = "{$storeUrl}/admin/api/{$apiVersion}/variants/{$variantId}.json";

            $payload = [
                "variant" => [
                    "id" => $variantId,
                    "price" => $newPrice
                ]
            ];

            $response = Http::withHeaders([
                "X-Shopify-Access-Token" => $accessToken,
                "Content-Type" => "application/json",
            ])->put($url, $payload);

            if ($response->successful()) {
                return [
                    "status" => "success",
                    "data" => $response->json()
                ];
            } else {
                return [
                    "status" => "error",
                    "code" => $response->status(),
                    "message" => $response->json()
                ];
            }

        } catch (\Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }


    

}
