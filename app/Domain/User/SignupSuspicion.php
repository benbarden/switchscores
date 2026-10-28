<?php

namespace App\Domain\User;

use App\Models\User;

/**
 * Why a signup looks suspicious, for the staff user lists. Flag only: staff judge, nothing is
 * blocked or deleted. The values behind each check live in config/signup-checks.php (from .env).
 */
class SignupSuspicion
{
    /**
     * @return string[] one reason per signal; empty if nothing looks wrong
     */
    public function reasons(User $user): array
    {
        $config = config('signup-checks');
        $reasons = [];

        $names = array_map('mb_strtolower', $config['names']);
        if ($names && in_array(mb_strtolower(trim((string) $user->display_name)), $names, true)) {
            $reasons[] = 'Display name';
        }

        $digits = $config['email_trailing_digits'];
        if ($digits > 0) {
            $localPart = strstr((string) $user->email, '@', true) ?: (string) $user->email;
            if (preg_match('/\d{'.$digits.',}$/', $localPart)) {
                $reasons[] = 'Email address';
            }
        }

        // Only users who signed up once signup_meta existed have the form timing.
        $meta = $user->signup_meta;
        if (is_array($meta)) {
            $seconds = $meta['seconds_to_submit'] ?? null;
            if ($seconds === null) {
                $reasons[] = 'Form token missing or invalid';
            } elseif ($config['min_seconds_to_submit'] > 0 && $seconds < $config['min_seconds_to_submit']) {
                $reasons[] = 'Form submitted in '.$seconds.'s';
            }
        }

        $hours = $config['verification_request_hours'];
        if ($hours > 0 && !$user->isEmailVerified() && !$user->verification_email_sent_at
            && $user->created_at && $user->created_at->lte(now()->subHours($hours))) {
            $reasons[] = 'No verification email requested';
        }

        return $reasons;
    }

    /**
     * Reasons for each suspicious user in a list, keyed by user ID.
     *
     * @return array<int, string[]>
     */
    public function forUsers(iterable $users): array
    {
        $flagged = [];
        foreach ($users as $user) {
            $reasons = $this->reasons($user);
            if ($reasons) {
                $flagged[$user->id] = $reasons;
            }
        }
        return $flagged;
    }
}
