<?php

namespace App\Domain\User;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Details of a signup request, saved to users.signup_meta so bogus signups can be spotted by
 * their patterns. For flagging, not blocking: anything here can be spoofed.
 *
 * The register form carries an encrypted timestamp of when it was shown, so the time taken to
 * submit it can be measured. Missing or tampered with, it's recorded as null.
 */
class SignupMeta
{
    const FORM_TOKEN_FIELD = 'form_token';

    const MAX_LENGTH = 500;

    public static function formToken(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    public function fromRequest(Request $request, bool $turnstileChecked): array
    {
        return [
            'ip' => $request->ip(),
            'country' => $request->header('CF-IPCountry'),
            'user_agent' => $this->limit($request->userAgent()),
            'accept_language' => $this->limit($request->header('Accept-Language')),
            'referrer' => $this->limit($request->header('Referer')),
            'seconds_to_submit' => $this->secondsToSubmit($request->input(self::FORM_TOKEN_FIELD)),
            'turnstile' => $turnstileChecked ? 'passed' : 'off',
        ];
    }

    private function secondsToSubmit(?string $token): ?int
    {
        if (empty($token)) {
            return null;
        }

        try {
            $shownAt = Crypt::decryptString($token);
        } catch (DecryptException) {
            return null;
        }

        if (!ctype_digit($shownAt)) {
            return null;
        }

        return max(0, now()->timestamp - (int) $shownAt);
    }

    private function limit(?string $value): ?string
    {
        return $value === null ? null : mb_substr($value, 0, self::MAX_LENGTH);
    }
}
