<?php

/**
 * Signals that flag a signup as suspicious on the staff user lists. Flag only: nothing is
 * blocked. The values are set in .env, not here, so the repo doesn't publish what's checked
 * for. Each check is off until its value is set.
 */
return [

    // Comma-separated display names, matched whole and ignoring case.
    'names' => array_filter(array_map('trim', explode(',', (string) env('SIGNUP_CHECK_NAMES', '')))),

    // Flag an email whose name part ends in at least this many digits. 0 = off.
    'email_trailing_digits' => (int) env('SIGNUP_CHECK_EMAIL_DIGITS', 0),

    // Flag a form submitted in fewer than this many seconds. 0 = off.
    'min_seconds_to_submit' => (int) env('SIGNUP_CHECK_MIN_SECONDS', 0),

    // Flag an unverified user who hasn't asked for a verification email this many hours
    // after signing up. 0 = off.
    'verification_request_hours' => (int) env('SIGNUP_CHECK_VERIFY_HOURS', 0),

];
