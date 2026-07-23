<?php

namespace App\Services\Daraja;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Generic Daraja HTTP client — ported from safestay-test/DarajaClient.php,
 * using Laravel's HTTP client instead of raw curl so it's mockable in tests
 * (Http::fake()). One instance is scoped to a single shortcode/app.
 */
class DarajaClient
{
    public function __construct(
        private readonly string $consumerKey,
        private readonly string $consumerSecret,
        private readonly string $baseUrl,
    ) {
    }

    public function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->timeout(20)
            ->get("{$this->baseUrl}/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        if (! $response->successful() || ! $response->json('access_token')) {
            throw new RuntimeException("Daraja token fetch failed (HTTP {$response->status()}): {$response->body()}");
        }

        return $response->json('access_token');
    }

    /**
     * Generic authenticated POST helper for any Daraja JSON endpoint.
     */
    public function post(string $path, array $payload, ?string $token = null): array
    {
        $token ??= $this->getAccessToken();

        $response = Http::withToken($token)
            ->timeout(30)
            ->post("{$this->baseUrl}{$path}", $payload);

        return [
            'http_code' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }
}
