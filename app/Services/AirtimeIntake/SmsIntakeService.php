<?php

namespace App\Services\AirtimeIntake;

use App\Models\Conversion;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Parses the transfer confirmation SMS the receiving line gets (forwarded by
 * a third-party SMS-forwarding app), matches it to a pending conversion by
 * type + network + sender number + amount, then triggers the same payout
 * path the admin's manual "mark received" button uses. Each carrier/product
 * phrases its confirmation SMS differently, so patterns are listed per case.
 */
class SmsIntakeService
{
    // [conversion type, network (null = not applicable), regex, sender capture group, amount capture group]
    private const PATTERNS = [
        // Safaricom Sambaza: "The subscriber 712345678 transferred 5.00 KSH for you."
        ['airtime', 'safaricom', '/subscriber\s+(\d+)\s+transferred\s+([\d.]+)\s*KSH/i', 1, 2],
        // Airtel Me2U: "You have received 50 KSH from 788063011. Your new balance is ..."
        ['airtime', 'airtel', '/received\s+([\d.]+)\s*KSH\s+from\s+(\d+)/i', 2, 1],
        // Bonga points gift: "The 715579172 subscriber transferred point 100 to you. Current point: 3292..."
        ['bonga', null, '/The\s+(\d+)\s+subscriber\s+transferred\s+point\s+([\d.]+)\s+to\s+you/i', 1, 2],
    ];

    public function __construct(private readonly IntakeInterface $intake)
    {
    }

    public function handle(string $smsText): void
    {
        foreach (self::PATTERNS as [$type, $network, $pattern, $senderGroup, $amountGroup]) {
            if (preg_match($pattern, $smsText, $matches)) {
                $this->processMatch($type, $network, $matches[$senderGroup], $matches[$amountGroup], $smsText);

                return;
            }
        }

        Log::warning('sms_intake_unrecognized_format', ['sms' => $smsText]);
    }

    private function processMatch(string $type, ?string $network, string $rawSender, string $rawAmount, string $smsText): void
    {
        try {
            $sender = PhoneNumber::normalize($rawSender);
        } catch (InvalidArgumentException) {
            Log::warning('sms_intake_bad_sender_number', ['raw' => $rawSender, 'sms' => $smsText]);

            return;
        }

        $amount = (int) round((float) $rawAmount);

        // Oldest-first: if the same sender/amount pair somehow matches more
        // than one pending conversion, fulfil whichever was requested first.
        $query = Conversion::query()
            ->where('type', $type)
            ->where('status', 'awaiting_intake')
            ->where('sender_number', $sender)
            ->where('amount_in', $amount)
            ->where('created_at', '>=', now()->subHour())
            ->oldest();

        // Bonga conversions don't have a network set (it's Safaricom-only,
        // and the app never sends one for type=bonga).
        if ($network !== null) {
            $query->where('network', $network);
        }

        $conversion = $query->first();

        if (! $conversion) {
            Log::warning('sms_intake_no_matching_conversion', [
                'type' => $type,
                'network' => $network,
                'sender' => $sender,
                'amount' => $amount,
                'sms' => $smsText,
            ]);

            return;
        }

        $this->intake->markReceived($conversion);
    }
}
