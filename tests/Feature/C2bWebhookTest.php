<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\BundleOrder;
use App\Models\C2bPayment;
use App\Models\C2bValidationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C2bWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function samplePayload(array $overrides = []): array
    {
        // Shape matches the fields the old c2b-confirmation/index.php read.
        return array_merge([
            'TransactionType' => 'Pay Bill',
            'TransID' => 'OEI2AK4Q16',
            'TransTime' => '20260723120000',
            'TransAmount' => '20',
            'BusinessShortCode' => '600990',
            'BillRefNumber' => '254712345678',
            'InvoiceNumber' => '',
            'MSISDN' => '254798765432',
            'FirstName' => 'Jane',
            'MiddleName' => '',
            'LastName' => 'Doe',
        ], $overrides);
    }

    public function test_validation_accepts_when_it_matches_a_pending_bundle_order(): void
    {
        $bundle = Bundle::factory()->create(['price' => 20]);
        BundleOrder::factory()->create([
            'bundle_id' => $bundle->id,
            'recipient_number' => '254712345678',
            'amount' => 20,
            'status' => 'pending_payment',
        ]);

        $response = $this->postJson('/api/webhooks/mpesa/c2b/validation', $this->samplePayload());

        $response->assertOk()->assertJson(['ResultCode' => 0]);
        $this->assertDatabaseHas('c2b_validation_log', ['decision' => 'accepted']);
    }

    public function test_validation_rejects_when_theres_no_matching_pending_order(): void
    {
        $response = $this->postJson('/api/webhooks/mpesa/c2b/validation', $this->samplePayload());

        $response->assertOk()->assertJson(['ResultCode' => 'C2B00016']);
        $this->assertDatabaseHas('c2b_validation_log', ['decision' => 'rejected']);
    }

    public function test_confirmation_marks_matching_bundle_order_paid(): void
    {
        $bundle = Bundle::factory()->create(['price' => 20]);
        $order = BundleOrder::factory()->create([
            'bundle_id' => $bundle->id,
            'recipient_number' => '254712345678',
            'amount' => 20,
            'status' => 'pending_payment',
        ]);

        $response = $this->postJson('/api/webhooks/mpesa/c2b/confirmation', $this->samplePayload());

        $response->assertOk()->assertJson(['ResultCode' => 0]);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('OEI2AK4Q16', $order->fresh()->mpesa_receipt);
    }

    public function test_confirmation_is_idempotent_on_duplicate_transaction_id(): void
    {
        $payload = $this->samplePayload();

        $this->postJson('/api/webhooks/mpesa/c2b/confirmation', $payload);
        $this->postJson('/api/webhooks/mpesa/c2b/confirmation', $payload);

        $this->assertSame(1, C2bPayment::where('transaction_id', 'OEI2AK4Q16')->count());
    }

    public function test_confirmation_always_acknowledges_even_with_no_match(): void
    {
        $response = $this->postJson('/api/webhooks/mpesa/c2b/confirmation', $this->samplePayload());

        $response->assertOk()->assertJson(['ResultCode' => 0]);
    }
}
