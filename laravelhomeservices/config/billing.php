<?php

declare(strict_types=1);

return [
    // Plan Pro para proveedores: precio en USD por periodo y duración del periodo.
    'pro_price' => (float) env('BILLING_PRO_PRICE', 8.00),
    'pro_period_days' => (int) env('BILLING_PRO_PERIOD_DAYS', 30),

    // Fracción de IVA incluida en el precio (0.15 = 15%). 0 si el negocio no cobra IVA
    // (p. ej. RIMPE negocio popular). Confírmalo con tu contador.
    'tax_rate' => (float) env('BILLING_TAX_RATE', 0),

    // Instrucciones de pago manual que verá el proveedor.
    'bank_transfer' => [
        'bank' => env('BILLING_BANK_NAME'),
        'account_type' => env('BILLING_BANK_ACCOUNT_TYPE', 'Ahorros'),
        'account_number' => env('BILLING_BANK_ACCOUNT_NUMBER'),
        'holder' => env('BILLING_BANK_HOLDER'),
        'holder_id' => env('BILLING_BANK_HOLDER_ID'),
    ],

    'payphone' => [
        'enabled' => (bool) env('PAYPHONE_ENABLED', false),
        'token' => env('PAYPHONE_TOKEN'),
        'store_id' => env('PAYPHONE_STORE_ID'),
        'base_url' => env('PAYPHONE_BASE_URL', 'https://pay.payphonetodoesposible.com'),
    ],
];
