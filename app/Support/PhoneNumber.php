<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Normalizes Kenyan phone numbers into the 2547XXXXXXXX / 2541XXXXXXXX
 * format Daraja requires. Client input arrives in all sorts of shapes
 * (0712345678, +254712345678, 254712345678, with spaces/dashes) and Daraja
 * silently rejects anything else — the STK push/B2C call still gets a
 * response, it just never reaches the phone.
 */
class PhoneNumber
{
    public static function normalize(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '254'.substr($digits, 1);
        } elseif (strlen($digits) === 9 && preg_match('/^[17]/', $digits)) {
            $digits = '254'.$digits;
        }

        if (! preg_match('/^254[17]\d{8}$/', $digits)) {
            throw new InvalidArgumentException("'{$raw}' doesn't look like a valid Kenyan phone number.");
        }

        return $digits;
    }
}
