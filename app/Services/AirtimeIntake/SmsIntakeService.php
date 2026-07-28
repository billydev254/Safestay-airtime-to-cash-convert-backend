<?php

namespace App\Services\AirtimeIntake;

use App\Models\Conversion;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Parses Safaricom's "Sambaza" airtime-transfer confirmation SMS (forwarded
 * by a third-party SMS-forwarding app on the receiving line), matches it to
 * a pending conversion by sender number + amount, then triggers the same
 * payout path the admin's manual "mark received" button uses.
 */
class SmsIntakeService
{
    private const PATTERN = '/subscriber\s+(\d+)\s+transferred\s+([\d.]+)\s*KSH/i';

    public function __construct(private readonly IntakeInterface $intake)
    {
    }

    public function handle(string $smsText): void
    {
        if (! preg_match(self::PATTERN, $smsText, $matches)) {
            Log::warning('sms_intake_unrecognized_format', ['sms' => $smsText]);

            return;
        }

        [, $rawSender, $rawAmount] = $matches;

        try {
            $sender = PhoneNumber::normalize($rawSender);
        } catch (InvalidArgumentException) {
            Log::warning('sms_intake_bad_sender_number', ['raw' => $rawSender, 'sms' => $smsText]);

            return;
        }

        $amount = (int) round((float) $rawAmount);

        // Oldest-first: if the same sender/amount pair somehow matches more
        // than one pending conversion, fulfil whichever was requested first.
        $conversion = Conversion::query()
            ->where('type', 'airtime')
            ->where('network', 'safaricom')
            ->where('status', 'awaiting_intake')
            ->where('sender_number', $sender)
            ->where('amount_in', $amount)
            ->where('created_at', '>=', now()->subHour())
            ->oldest()
            ->first();

        if (! $conversion) {
            Log::warning('sms_intake_no_matching_conversion', [
                'sender' => $sender,
                'amount' => $amount,
                'sms' => $smsText,
            ]);

            return;
        }

        $this->intake->markReceived($conversion);
    }
}
