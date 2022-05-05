<?php
return [
    'locale' => 'zh',
    'fallback_locale' => 'en',
    'available_locales' => [
        'English' => 'en',
        'Russian' => 'ru',
        'French' => 'fr',
        'Chinese' => 'cn',
    ],

    'tiny' => [
        'contract_address',
    ],
    'debug' => (bool) env('APP_DEBUG', true),
    'env' => env('APP_ENV', 'production'),  

    'ethereum_rpc' => [
        'kovan' => [
            env('ETH_KOVAN_SERVER_URL'),
        ],
        'mainnet' => [
            env('ETH_SERVER_URL'),
        ],
    ],

    'whitelist' =>  [
        'require_invite_user'    =>  2,
        'require_trade_volume'   =>  0.3
    ],

    
];