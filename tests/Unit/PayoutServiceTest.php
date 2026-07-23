<?php

namespace Tests\Unit;

use App\Models\Conversion;
use App\Services\Payout\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_daraja_request_exception_marks_conversion_payout_failed_instead_of_stuck_paying(): void
    {
        // Simulates what actually happens with unconfigured/invalid Daraja
        // credentials — the HTTP client throws before any response comes
        // back, so there's a real risk of leaving the conversion stuck in
        // "paying" forever if that exception isn't handled.
        Http::fake(function () {
            throw new ConnectionException('Could not resolve host');
        });

        $conversion = Conversion::factory()->create(['status' => 'awaiting_intake']);

        app(PayoutService::class)->payout($conversion);

        $conversion->refresh();
        $this->assertSame('payout_failed', $conversion->status);
        $this->assertNotNull($conversion->originator_conversation_id);
        $this->assertStringContainsString('Could not reach Daraja', $conversion->payout_result_desc);
    }

    public function test_successful_dispatch_leaves_conversion_in_paying_awaiting_the_result_webhook(): void
    {
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'fake-token']),
            '*/mpesa/b2c/v3/paymentrequest' => Http::response(['ResponseCode' => '0']),
        ]);

        $conversion = Conversion::factory()->create(['status' => 'awaiting_intake']);

        app(PayoutService::class)->payout($conversion);

        $this->assertSame('paying', $conversion->fresh()->status);
    }
}
