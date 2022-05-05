<?php

return [

    'url'   =>  env('APP_URL',''),

    'site_url'  =>  env('SITE_URL',''),

    'static_url'   =>  env('STATIC_URL',''),

    'data_api_url'   =>  env('DATA_API_URL',''),

    'allow_language' => [
        'en',
        'zh',
        'id',
        'ja',
        'ko',
        'fr',
        'de',
        'fi',
        'la',
        'ms',
        'th'
    ],

    'duck_token'  =>  [
        'address'   =>  env('DUCK_ADDRESS',''),
        'decimals'  =>  env('DUCK_DECIMALS',18),
    ],

    'must_use_invite_code'  =>  env('USE_INVITE_CODE',true),

];
