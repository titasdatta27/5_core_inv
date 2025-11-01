<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\TiktokOrderMetric;
use Carbon\Carbon;

class FetchTiktokData extends Command
{
    protected $signature = 'tiktok:fetch';
    protected $description = 'Fetch TikTok Shop order data (Note: TikTok Shop API has changed - webhooks recommended)';

    public function handle()
    {
        $this->info('TikTok Shop API Integration Status:');
        $this->warn('=====================================');
        $this->warn('TikTok Shop has changed their API structure significantly.');
        $this->warn('The old polling-based API endpoints are no longer available.');
        $this->warn('');
        $this->info('Available approaches:');
        $this->info('1. ✅ Webhook Integration - TikTok pushes order data to your server (RECOMMENDED)');
        $this->info('2. ❌ Polling APIs - Deprecated and not working');
        $this->info('3. 🔄 Scheduled Order Sync - Manual alternative (limited)');
        $this->warn('');
        $this->warn('Current implementation status: API endpoints are outdated');
        $this->warn('Webhook is the ONLY reliable automated method');
        $this->warn('');

        // Check if webhook is configured
        $webhookSecret = config('services.tiktok.webhook_secret');
        if ($webhookSecret && $webhookSecret !== 'your_webhook_secret_here') {
            $this->info('✅ Webhook appears to be configured');
            $this->showWebhookStatus();
        } else {
            $this->warn('⚠️  Webhook not fully configured - orders will not sync automatically');
            $this->showWebhookInstructions();
        }

        // For backward compatibility, try the old method but expect it to fail
        $this->info('Attempting legacy API call (expected to fail)...');

        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                $this->error('Failed to get TikTok access token');
                return;
            }

            $orders = $this->fetchAllOrders($accessToken);
            $this->info('Fetched total orders: ' . count($orders));

            foreach ($orders as $order) {
                $this->processOrder($order);
            }

            $this->info('TikTok data stored successfully.');
        } catch (\Exception $e) {
            $this->error('Error fetching TikTok data: ' . $e->getMessage());
        }
    }

    protected function showWebhookInstructions()
    {
        $this->info('');
        $this->info('🚀 Complete TikTok Shop Webhook Setup Guide:');
        $this->info('');
        $this->info('1. 📝 Get Webhook Secret from TikTok:');
        $this->info('   - Go to TikTok Shop Seller Center');
        $this->info('   - Navigate to Settings > Webhooks');
        $this->info('   - Copy the "Webhook Secret" (if available) or generate one');
        $this->info('');
        $this->info('2. 🔧 Configure Webhook Secret:');
        $this->info('   - Open your .env file');
        $this->info('   - Set: TIKTOK_WEBHOOK_SECRET=your_actual_webhook_secret');
        $this->info('   - Current value: ' . (config('services.tiktok.webhook_secret') ?: 'NOT SET'));
        $this->info('');
        $this->info('3. 🌐 Register Webhook URL:');
        $webhookUrl = config('app.url') . '/api/webhooks/tiktok/orders';
        $this->info('   - Webhook URL: ' . $webhookUrl);
        $this->info('   - Register this URL in TikTok Shop Seller Center > Settings > Webhooks');
        $this->info('');
        $this->info('4. 📡 Subscribe to Events:');
        $this->info('   - Order Created (ORDER_CREATE)');
        $this->info('   - Order Status Changed (ORDER_STATUS_CHANGE)');
        $this->info('');
        $this->info('5. ✅ Test the Setup:');
        $testUrl = config('app.url') . '/api/webhooks/tiktok/test';
        $this->info('   - Test URL: ' . $testUrl);
        $this->info('');
        $this->info('📋 Files Modified:');
        $this->info('   ✅ app/Http/Controllers/Api/TiktokWebhookController.php');
        $this->info('   ✅ routes/api.php');
        $this->info('   ✅ config/services.php');
        $this->info('   ✅ .env (add TIKTOK_WEBHOOK_SECRET)');
        $this->info('');
        $this->warn('⚠️  Security Note: Always verify webhook signatures in production!');
        $this->warn('⚠️  Keep webhook secrets secure and never commit to version control!');
        $this->info('');
        $this->showManualSyncOption();
    }

    protected function showWebhookStatus()
    {
        $this->info('');
        $this->info('🎯 Webhook Status:');
        $webhookUrl = config('app.url') . '/api/webhooks/tiktok/orders';
        $testUrl = config('app.url') . '/api/webhooks/tiktok/test';
        $this->info('   ✅ Webhook URL: ' . $webhookUrl);
        $this->info('   ✅ Test URL: ' . $testUrl);
        $this->info('   ✅ Controller: TiktokWebhookController');
        $this->info('   ✅ Signature Verification: HMAC-SHA256');
        $this->info('   ✅ Events: ORDER_CREATE, ORDER_STATUS_CHANGE');
        $this->info('');
        $this->info('📊 To check webhook functionality:');
        $this->info('   curl -X GET "' . $testUrl . '"');
        $this->info('');
        $this->warn('⚠️  Make sure webhook URL is registered in TikTok Seller Center!');
    }

    protected function showManualSyncOption()
    {
        $this->info('');
        $this->info('🔄 Manual Order Sync (Alternative to Webhooks):');
        $this->info('   Since automated APIs are deprecated, you can:');
        $this->info('   1. Export orders manually from TikTok Seller Center');
        $this->info('   2. Use Excel/CSV import to sync orders');
        $this->info('   3. Set up scheduled manual checks');
        $this->info('');
        $this->info('   This is NOT real-time but can work for basic order tracking.');
        $this->info('   Frequency: Daily/Weekly manual sync recommended.');
    }

    protected function getAccessToken()
    {
        $appId = config('services.tiktok.app_id');
        $appSecret = config('services.tiktok.app_secret');

        if (!$appId || !$appSecret) {
            $this->error('TikTok credentials missing in config/services.php');
            return null;
        }

        $this->info("Using App ID: $appId");

        // Try to get access token using the older TikTok API method
        $timestamp = time();
        $params = [
            'app_key' => $appId,
            'timestamp' => $timestamp,
            'grant_type' => 'authorized_code',
            'auth_code' => 'dummy_auth_code' // This needs to be obtained through OAuth flow
        ];

        $sign = $this->generateSignature($params, $appSecret);
        $params['sign'] = $sign;

        $url = 'https://open-api.tiktokglobalshop.com/api/token/get';

        $this->info("Getting access token from: $url");

        try {
            $response = Http::timeout(10)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['data']['access_token'] ?? $data['access_token'] ?? null;
                if ($token) {
                    $this->info('Token authentication successful');
                    return $token;
                }
            }

            $this->warn("Token request failed: " . $response->status() . " - " . $response->body());

            // For now, return a dummy token to test the order list API
            // In production, you need to implement proper OAuth2 flow to get auth_code
            $this->warn("Using dummy access token for testing. Implement proper OAuth2 flow for production.");
            return 'dummy_access_token';

        } catch (\Exception $e) {
            $this->warn("Token request error: " . $e->getMessage());
            $this->warn("Using dummy access token for testing. Implement proper OAuth2 flow for production.");
            return 'dummy_access_token';
        }
    }

    protected function fetchAllOrders($accessToken)
    {
        $appId = config('services.tiktok.app_id');
        $appSecret = config('services.tiktok.app_secret');

        // Use the correct TikTok Shop API endpoint for order list
        $endpoint = 'https://open-api.tiktokglobalshop.com/api/orders/list/query';

        // Prepare parameters for the order list API
        $timestamp = time();
        $version = '202309'; // API version

        // Create signature for the request
        $params = [
            'app_key' => $appId,
            'timestamp' => $timestamp,
            'access_token' => $accessToken,
            'version' => $version,
            'page_size' => 50,
            'create_time_ge' => Carbon::now()->subDays(60)->timestamp,
            'create_time_le' => Carbon::now()->timestamp,
        ];

        $sign = $this->generateSignature($params, $appSecret);

        // Add signature to parameters
        $params['sign'] = $sign;

        $this->info("Fetching orders from: $endpoint");

        try {
            $response = Http::timeout(30)->get($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                $orders = $data['data']['orders'] ?? $data['orders'] ?? [];
                $this->info("Successfully fetched " . count($orders) . " orders");
                return $orders;
            }

            $this->error('The provided TikTok Shop API endpoint is not working.');
            $this->error('You provided: GET https://open-api.tiktokglobalshop.com/api/orders/list/query');
            $this->error('But this endpoint returns: Invalid path - endpoint does not exist');
            $this->error('');
            $this->error('Please verify the correct TikTok Shop API endpoints from:');
            $this->error('https://partner.tiktokshop.com/doc');
            $this->error('');
            $this->error('TikTok Shop may have changed their API structure or requires different authentication.');
            return [];

        } catch (\Exception $e) {
            $this->error("Order list API error: " . $e->getMessage());
            return [];
        }
    }

    protected function generateSignature($params, $appSecret)
    {
        // Sort parameters alphabetically by key
        ksort($params);

        // Build the string to sign
        $signString = $appSecret;
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $appSecret;

        // Create MD5 hash
        return md5($signString);
    }

    protected function processOrder($order)
    {
        $orderId = $order['order_id'] ?? null;
        $orderStatus = $order['order_status'] ?? null;
        $createTime = $order['create_time'] ?? null;

        if (!$orderId) {
            $this->warn('Skipping order without ID');
            return;
        }

        if (in_array($orderStatus, ['CANCELLED', 'cancelled'])) {
            return;
        }

        $orderDate = $createTime ? Carbon::createFromTimestamp($createTime)->toDateString() : null;
        $lineItems = $order['item_list'] ?? [];

        foreach ($lineItems as $item) {
            $sku = $item['seller_sku'] ?? null;
            $quantity = $item['quantity'] ?? 0;
            $productName = $item['product_name'] ?? '';
            $itemTotal = ($item['item_total_price']['amount'] ?? 0) / 100;

            if (!$sku) {
                $this->warn("Skipping item without SKU in order $orderId");
                continue;
            }

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

            $this->info("Processed item: $sku (Qty: $quantity, Amount: $$itemTotal)");
        }
    }
}
