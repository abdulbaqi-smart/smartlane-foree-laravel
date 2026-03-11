<?php

return [
    'base_url' => env('FOREE_BASE_URL', 'https://api-sandbox.foreebill.com'),
    'api_key'  => env('FOREE_API_KEY'),

    // Retry policy for transient errors (timeouts, 5xx)
    'timeout'  => (int) env('FOREE_TIMEOUT', 20),
    'retries'  => (int) env('FOREE_RETRIES', 2),
    'retry_ms' => (int) env('FOREE_RETRY_MS', 300),

    // Webhook hardening — comma separated IPs from Foree
    'webhook_ip_allowlist' => array_filter(
        explode(',', (string) env('FOREE_WEBHOOK_IP_ALLOWLIST', ''))
    ),

    // If Foree provides a shared secret later
    'webhook_secret' => env('FOREE_WEBHOOK_SECRET'),
];
