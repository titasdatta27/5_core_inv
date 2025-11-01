<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTiktokWebhook extends Command
{
    protected $signature = 'tiktok:test-webhook {secret?}';
    protected $description = 'Test TikTok webhook with a sample payload';

    public function handle()
    {
        $webhookSecret = $this->argument('secret') ?? config('services.tiktok.webhook_secret');

        if (!$webhookSecret || $webhookSecret === 'your_webhook_secret_here') {
            $this->error('❌ Webhook secret not configured!');
            $this->info('Please provide webhook secret:');
            $this->info('php artisan tiktok:test-webhook YOUR_ACTUAL_SECRET');
            $this->info('');
            $this->info('Or update .env file:');
            $this->info('TIKTOK_WEBHOOK_SECRET=your_actual_webhook_secret');
            return;
        }

        $this->info('🧪 Testing TikTok Webhook...');
        $this->info('Webhook Secret: ' . substr($webhookSecret, 0, 10) . '...');

        // Sample TikTok order webhook payload
        $samplePayload = [
            'type' => 'ORDER_CREATE',
            'timestamp' => time(),
            'data' => [
                'order_id' => 'TEST_ORDER_' . time(),
                'order_status' => 'UNPAID',
                'create_time' => time(),
                'item_list' => [
                    [
                        'seller_sku' => 'TEST_SKU_001',
                        'product_name' => 'Test Product',
                        'quantity' => 2,
                        'item_total_price' => [
                            'amount' => 5000, // $50.00 in cents
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ]
        ];

        $jsonPayload = json_encode($samplePayload);

        // Generate signature like TikTok does
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $webhookSecret);

        $webhookUrl = config('app.url') . '/api/webhooks/tiktok/orders';

        $this->info('📡 Sending test webhook to: ' . $webhookUrl);
        $this->info('📦 Payload type: ' . $samplePayload['type']);
        $this->info('🔐 Signature: ' . substr($signature, 0, 20) . '...');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Tt-Signature' => $signature,
                    'User-Agent' => 'TikTok-Webhook-Test/1.0'
                ])
                ->post($webhookUrl, $samplePayload);

            if ($response->successful()) {
                $this->info('✅ Webhook test successful!');
                $this->info('Response: ' . $response->body());
            } else {
                $this->error('❌ Webhook test failed!');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('❌ Webhook test error: ' . $e->getMessage());
        }

        $this->info('');
        $this->info('📋 Next Steps:');
        $this->info('1. If test passed: Register webhook URL in TikTok Seller Center');
        $this->info('2. Subscribe to ORDER_CREATE and ORDER_STATUS_CHANGE events');
        $this->info('3. Monitor logs for real TikTok webhooks');
    }
}