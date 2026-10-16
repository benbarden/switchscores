<?php

namespace App\Domain\Relay;

/**
 * Sends a request out through a Cloudflare Worker rather than from the server's own IP.
 *
 * Some sites refuse requests from datacentre IPs. Tested 2026-10-16 from infrared-switch
 * (DigitalOcean): the Nintendo price API returned 403 and six review feeds served bot check
 * pages, while the same requests through the Worker got real prices and full RSS (#162, #174).
 *
 * The Worker (ss-relay, in the Cloudflare dashboard) takes the target as ?url=, needs the
 * shared secret in X-Relay-Key, only does HTTPS GET, and only fetches hosts on its own
 * allowlist - so a host must be added there before anything here can reach it through it.
 *
 * Configured by RELAY_URL / RELAY_KEY (config/services.php). Unset means "not configured",
 * and callers go direct - which is right for localdev.
 */
class EgressRelay
{
    const KEY_HEADER = 'X-Relay-Key';

    /**
     * The Worker's own refusals, as plain-text bodies. Anything else is the target's response,
     * passed through - so a 403 from the target isn't mistaken for the relay refusing us.
     */
    const REFUSALS = [
        'Forbidden' => 'the relay rejected the key (check RELAY_KEY matches the Worker secret)',
        'Host not allowed' => "the relay refused the host (add it to the Worker's allowlist)",
        'Bad url' => 'the relay could not read the target URL',
    ];

    public function __construct(
        private ?string $url = null,
        private ?string $key = null
    )
    {
    }

    public static function fromConfig(): self
    {
        return new self(config('services.egress_relay.url'), config('services.egress_relay.key'));
    }

    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->key);
    }

    /**
     * The URL to request in place of $targetUrl.
     */
    public function relayUrl(string $targetUrl): string
    {
        $separator = str_contains($this->url, '?') ? '&' : '?';
        return $this->url.$separator.'url='.rawurlencode($targetUrl);
    }

    public function headers(): array
    {
        return [self::KEY_HEADER => $this->key];
    }

    /**
     * If the response is the Worker refusing the request (rather than the target's own
     * response), a short explanation; otherwise null.
     */
    public function refusal(int $statusCode, string $body): ?string
    {
        if ($statusCode !== 400 && $statusCode !== 403) {
            return null;
        }
        return self::REFUSALS[trim($body)] ?? null;
    }
}
