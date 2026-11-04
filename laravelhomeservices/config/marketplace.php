<?php

declare(strict_types=1);

return [
    'cache_ttl_seconds' => (int) env('MARKETPLACE_CACHE_TTL', 3600),
    'free_service_limit' => (int) env('FREE_SERVICE_LIMIT', 3),
    'lead_notification_address' => env('LEAD_NOTIFICATION_ADDRESS'),

    // Public contact details shown in the header/footer and used as the
    // WhatsApp fallback for services that are not owned by a provider.
    'contact_phone' => env('MARKETPLACE_CONTACT_PHONE', '0995528556'),
    'contact_email' => env('MARKETPLACE_CONTACT_EMAIL', 'gameri7@hotmail.com'),
    'whatsapp_country_code' => env('MARKETPLACE_WHATSAPP_COUNTRY_CODE', '593'),

    // Zones a provider can cover (slug => label). Malacatos is the main one and is listed first.
    // Keep slugs stable: they are stored in provider_zones and service_requests.
    'zones' => [
        'malacatos' => 'Malacatos',
        'vilcabamba' => 'Vilcabamba',
        'yangana' => 'Yangana',
        'quinara' => 'Quinara',
    ],
    'main_zone' => 'malacatos',

    // redis: contadores en memoria que sync-visit-counters vuelca a la base. database: escritura directa (sin Redis).
    'visit_counter' => env('VISIT_COUNTER', 'redis'),
];
