<?php

use App\Support\Security\HoneypotSpamResponder;

return [
    'enabled' => env('HONEYPOT_ENABLED', env('APP_ENV') !== 'testing'),
    'name_field_name' => env('HONEYPOT_NAME', 'company_website'),
    'randomize_name_field_name' => env('HONEYPOT_RANDOMIZE', true),
    'valid_from_timestamp' => env('HONEYPOT_VALID_FROM_TIMESTAMP', true),
    'valid_from_field_name' => env('HONEYPOT_VALID_FROM', 'valid_from'),
    'amount_of_seconds' => (int) env('HONEYPOT_SECONDS', 2),
    'respond_to_spam_with' => HoneypotSpamResponder::class,
    'honeypot_fields_required_for_all_forms' => false,
    'spam_protection' => \Spatie\Honeypot\SpamProtection::class,
    'with_csp' => env('HONEYPOT_WITH_CSP', false),
];
