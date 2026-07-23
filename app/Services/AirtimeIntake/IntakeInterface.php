<?php

namespace App\Services\AirtimeIntake;

use App\Models\Conversion;

/**
 * Detecting that a customer's airtime/Bonga-points transfer has actually
 * landed is still unresolved (pending the client's answer on receiving-SIM
 * setup) — this seam lets Phase 1 ship with a manual admin action and swap
 * in real detection later without touching the rest of the pipeline.
 */
interface IntakeInterface
{
    public function markReceived(Conversion $conversion): void;
}
