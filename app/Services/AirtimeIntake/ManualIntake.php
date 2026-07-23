<?php

namespace App\Services\AirtimeIntake;

use App\Models\Conversion;
use App\Services\Payout\PayoutService;

/**
 * Phase 1 implementation: the admin dashboard's "mark airtime received"
 * button calls this directly. Swap for a real SMS-detection-based
 * implementation later without touching callers.
 */
class ManualIntake implements IntakeInterface
{
    public function __construct(private readonly PayoutService $payoutService)
    {
    }

    public function markReceived(Conversion $conversion): void
    {
        $this->payoutService->payout($conversion);
    }
}
