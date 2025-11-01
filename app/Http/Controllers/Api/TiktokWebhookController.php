<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TiktokOrderMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TiktokWebhookController extends Controller
{
    public function handleOrderWebhook(Request $request)
    {
        try {
            // Log the incoming webhook
            Log::info('TikTok Webhook Received', [
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'body_length' => strlen($request->getContent())
            ]);

            // Validate TikTok signature (implement signature verification)
            if (!$this->verifySignature($request)) {
                Log::warning('TikTok webhook signature verification failed', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all()
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->all();

            // Log successful signature verification
            Log::info('TikTok webhook signature verified successfully', [
                'type' => $data['type'] ?? 'unknown',
                'timestamp' => $data['timestamp'] ?? null
            ]);

            // Handle different webhook types
            $webhookType = $data['type'] ?? null;

            switch ($webhookType) {
                case 'ORDER_STATUS_CHANGE':
                case 'ORDER_CREATE':
                    $this->processOrderData($data['data'] ?? []);
                    break;

                default:
                    Log::info('Unhandled TikTok webhook type: ' . $webhookType, ['data' => $data]);
                    break;
            }

            return response()->json(['code' => 0, 'message' => 'success']);

        } catch (\Exception $e) {
            Log::error('TikTok webhook processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function verifySignature(Request $request)
    {
        // TikTok webhook signature verification
        // TikTok uses X-Tt-Signature header for signature verification

        $signature = $request->header('X-Tt-Signature');
        if (!$signature) {
            Log::warning('TikTok webhook: Missing X-Tt-Signature header');
            return false;
        }

        // Get the webhook secret from config (you need to set this in TikTok Seller Center)
        $webhookSecret = config('services.tiktok.webhook_secret');
        if (!$webhookSecret) {
            Log::error('TikTok webhook secret not configured. Set TIKTOK_WEBHOOK_SECRET in .env');
            return false; // For testing, you might want to return true
        }

        // Get raw request body
        $body = $request->getContent();

        // Create expected signature using HMAC-SHA256
        $expectedSignature = hash_hmac('sha256', $body, $webhookSecret);

        // TikTok typically prefixes the signature
        $expectedSignatureWithPrefix = 'sha256=' . $expectedSignature;

        // Verify signature
        $isValid = hash_equals($expectedSignatureWithPrefix, $signature);

        if (!$isValid) {
            Log::warning('TikTok webhook signature verification failed', [
                'received' => $signature,
                'expected' => $expectedSignatureWithPrefix
            ]);
        }

        return $isValid;
    }

    protected function processOrderData($orderData)
    {
        if (!isset($orderData['order_id'])) {
            Log::warning('TikTok webhook: Missing order_id', $orderData);
            return;
        }

        $orderId = $orderData['order_id'];
        $orderStatus = $orderData['order_status'] ?? 'UNKNOWN';
        $createTime = $orderData['create_time'] ?? null;

        // Skip cancelled orders
        if (in_array(strtolower($orderStatus), ['cancelled', 'canceled'])) {
            Log::info("Skipping cancelled order: $orderId");
            return;
        }

        $orderDate = $createTime ? Carbon::createFromTimestamp($createTime)->toDateString() : null;

        // Process line items
        $lineItems = $orderData['item_list'] ?? [];

        foreach ($lineItems as $item) {
            $sku = $item['seller_sku'] ?? $item['sku'] ?? null;
            $quantity = $item['quantity'] ?? 0;
            $productName = $item['product_name'] ?? '';
            $itemTotal = ($item['item_total_price']['amount'] ?? 0) / 100; // Convert from cents

            if (!$sku) {
                Log::warning("Skipping item without SKU in order $orderId", $item);
                continue;
            }

            // Store order metric
            TiktokOrderMetric::updateOrCreate(
                [
                    'order_number' => $orderId,
                    'sku' => $sku
                ],
                [
                    'order_date' => $orderDate,
                    'status' => $orderStatus,
                    'amount' => $itemTotal,
                    'display_sku' => $productName,
                    'sku' => $sku,
                    'quantity' => $quantity,
                ]
            );

            Log::info("Processed TikTok order item: $sku (Qty: $quantity, Amount: $$itemTotal)");
        }
    }

    public function testWebhook(Request $request)
    {
        // Simple test endpoint to verify webhook URL is accessible
        return response()->json([
            'status' => 'success',
            'message' => 'TikTok webhook endpoint is working',
            'timestamp' => now(),
            'url' => config('app.url') . '/api/webhooks/tiktok/orders'
        ]);
    }
}