<?php

declare(strict_types=1);

return [
    'enabled'      => (bool) env('SSO_ENABLED', false),
    'url'          => rtrim((string) env('SSO_URL', 'http://localhost:8087'), '/'),
    'internal_url' => rtrim((string) env('SSO_INTERNAL_URL', env('SSO_URL', 'http://localhost:8087')), '/'),
    'client_id'    => (string) env('SSO_CLIENT_ID', ''),
    'client_secret' => (string) env('SSO_CLIENT_SECRET', ''),
    'redirect_uri'  => (string) env('SSO_REDIRECT_URI', ''),
];
