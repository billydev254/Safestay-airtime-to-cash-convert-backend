<?php

namespace Tests\Feature;

use App\Models\B2cResult;
use App\Models\Conversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class B2cWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function samplePayload(string $originatorConversationId, int $resultCode = 0): array
    {
        // Shape matches Result.ResultParameters the old index.php/indextimeout.php read.
        return [
            'Result' => [
                'ResultType' => 0,
                'ResultCode' => $resultCode,
                'ResultDesc' => $resultCode === 0 ? 'The service request is processed successfully.' : 'Insufficient funds.',
                'OriginatorConversationID' => $originatorConversationId,
                'ConversationID' => 'AG_20260723_0000',
                'TransactionID' => 'OEI2AK4Q17',
                'ResultParameters' => [
                    'ResultParameter' => [
                        ['Key' => 'TransactionAmount', 'Value' => 400],
                        ['Key' => 'TransactionReceipt', 'Value' => 'OEI2AK4Q17'],
                        ['Key' => 'ReceiverPartyPublicName', 'Value' => '254712345678 - Jane Doe'],
                        ['Key' => 'B2CUtilityAccountAvailableFunds', 'Value' => 15000],
                        ['Key' => 'TransactionCompletedDateTime', 'Value' => '23.07.2026 12:00:00'],
                    ],
                ],
            ],
        ];
    }

    public function test_successful_result_marks_conversion_paid(): void
    {
        $conversion = Conversion::factory()->create([
            'status' => 'paying',
            'originator_conversation_id' => 'orig-conv-1',
        ]);

        $response = $this->postJson(
            '/api/webhooks/mpesa/b2c/result',
            $this->samplePayload('orig-conv-1')
        );

        $response->assertOk()->assertJson(['ResultCode' => 0]);
        $this->assertSame('paid', $conversion->fresh()->status);
        $this->assertSame('OEI2AK4Q17', $conversion->fresh()->mpesa_receipt);
    }

    public function test_failed_result_marks_conversion_payout_failed(): void
    {
        $conversion = Conversion::factory()->create([
            'status' => 'paying',
            'originator_conversation_id' => 'orig-conv-2',
        ]);

        $response = $this->postJson(
            '/api/webhooks/mpesa/b2c/result',
            $this->samplePayload('orig-conv-2', resultCode: 1)
        );

        $response->assertOk();
        $this->assertSame('payout_failed', $conversion->fresh()->status);
    }

    public function test_result_is_idempotent_on_duplicate_originator_conversation_id(): void
    {
        Conversion::factory()->create([
            'status' => 'paying',
            'originator_conversation_id' => 'orig-conv-3',
        ]);

        $payload = $this->samplePayload('orig-conv-3');

        $this->postJson('/api/webhooks/mpesa/b2c/result', $payload);
        $this->postJson('/api/webhooks/mpesa/b2c/result', $payload);

        $this->assertSame(1, B2cResult::where('originator_conversation_id', 'orig-conv-3')->count());
    }

    public function test_timeout_route_uses_the_same_handler(): void
    {
        $conversion = Conversion::factory()->create([
            'status' => 'paying',
            'originator_conversation_id' => 'orig-conv-4',
        ]);

        $response = $this->postJson(
            '/api/webhooks/mpesa/b2c/timeout',
            $this->samplePayload('orig-conv-4', resultCode: 1)
        );

        $response->assertOk();
        $this->assertSame('payout_failed', $conversion->fresh()->status);
    }
}
