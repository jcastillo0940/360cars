<?php

return [
    'cache_hours' => env('EXCHANGE_RATES_CACHE_HOURS', 12),
    'test_usd_to_crc' => env('EXCHANGE_RATES_TEST_USD_TO_CRC', 505.0),
    'providers' => [
        [
            'name' => 'open.er-api',
            'url' => 'https://open.er-api.com/v6/latest/USD',
        ],
        [
            'name' => 'frankfurter',
            'url' => 'https://api.frankfurter.app/latest?from=USD&to=CRC',
        ],
    ],
];
