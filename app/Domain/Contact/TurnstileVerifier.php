<?php

namespace App\Domain\Contact;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verify a Cloudflare Turnstile token server-side.
     *
     * If no secret is configured (e.g. local dev without keys), verification
     * is skipped and treated as a pass so the form remains usable.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret');

        if (empty($secret)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->successful() && $response->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed: '.$e->getMessage());
            return false;
        }
    }
}
