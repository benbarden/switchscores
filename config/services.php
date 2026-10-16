<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => \App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/twitter/callback',
        //'redirect' => route('auth.login.twitter.callback'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET'),
    ],

    // Where contact form notifications are sent.
    'contact' => [
        'notify_address' => env('CONTACT_NOTIFY_ADDRESS', 'ben@switchscores.com'),
    ],

    // Outbound relay (a Cloudflare Worker) for requests that sites refuse from the server's
    // datacentre IP: the Nintendo price API, and review feeds marked "fetch via relay".
    // Leave unset on localdev - a residential IP gets through directly. See App\Domain\Relay\EgressRelay.
    'egress_relay' => [
        'url' => env('RELAY_URL'),
        'key' => env('RELAY_KEY'),
    ],

];
