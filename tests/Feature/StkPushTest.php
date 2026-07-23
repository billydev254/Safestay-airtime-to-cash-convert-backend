<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\BundleOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StkPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'daraja.till.shortcode' => '600990',
            'daraja.till.consumer_key' => 'test-key',
            'daraja.till.consumer_secret' => 'test-secret',
            'daraja.till.passkey' => 'test-passkey',
        ]);
    }

    public function test_store_creates_pending_order_and_returns_checkout_request_id(): void
    {
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'fake-token']),
            '*/mpesa/stkpush/v1/processrequest' => Http::response([
                'ResponseCode' => '0',
                'CheckoutRequestID' => 'ws_CO_123',
            ]),
        ]);

        $bundle = Bundle::factory()->create(['price' => 20]);

        $response = $this->postJson('/api/stk-push', [
            'bundle_id' => $bundle->id,
            'recipient_number' => '254712345678',
            'mpesa_number' => '254798765432',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('bundle_orders', [
            'bundle_id' => $bundle->id,
            'recipient_number' => '254712345678',
            'checkout_request_id' => 'ws_CO_123',
            'status' => 'pending_payment',
        ]);
    }

    public function test_store_marks_order_failed_when_daraja_rejects(): void
    {
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'fake-token']),
            '*/mpesa/stkpush/v1/processrequest' => Http::response(['errorMessage' => 'Bad request'], 400),
        ]);

        $bundle = Bundle::factory()->create(['price' => 20]);

        $response = $this->postJson('/api/stk-push', [
            'bundle_id' => $bundle->id,
            'recipient_number' => '254712345678',
            'mpesa_number' => '254798765432',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('bundle_orders', ['status' => 'failed']);
    }

    public function test_callback_marks_order_paid_on_success(): void
    {
        $bundle = Bundle::factory()->create(['price' => 20]);
        $order = BundleOrder::factory()->create([
            'bundle_id' => $bundle->id,
            'checkout_request_id' => 'ws_CO_456',
            'status' => 'pending_payment',
        ]);

        $response = $this->postJson('/api/webhooks/mpesa/stk-push/callback', [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_456',
                    'ResultCode' => 0,
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'OEI2AK4Q18'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('OEI2AK4Q18', $order->fresh()->mpesa_receipt);
    }

    public function test_callback_marks_order_failed_when_user_cancels(): void
    {
        $bundle = Bundle::factory()->create(['price' => 20]);
        $order = BundleOrder::factory()->create([
            'bundle_id' => $bundle->id,
            'checkout_request_id' => 'ws_CO_789',
            'status' => 'pending_payment',
        ]);

        $response = $this->postJson('/api/webhooks/mpesa/stk-push/callback', [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_789',
                    'ResultCode' => 1032,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('failed', $order->fresh()->status);
    }
}
