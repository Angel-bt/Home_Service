<?php

declare(strict_types=1);

return [
    'cache_ttl_seconds' => (int) env('MARKETPLACE_CACHE_TTL', 3600),
    'free_service_limit' => (int) env('FREE_SERVICE_LIMIT', 3),
    'lead_notification_address' => env('LEAD_NOTIFICATION_ADDRESS'),
];
