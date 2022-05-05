<?php

/*
recaptcha
 */

return [
    'hooks' => [
        'normal'        =>  'https://discord.com/api/webhooks/934045129008906310/vU5dgKb7_6-fwnSwagXDyKNz28RAdTKSCJeIi3zc3ziAGLTlyRSzZ7xKCpc5WVKdJ4mU',
        'emergency'     =>  'https://discord.com/api/webhooks/934049590422937650/gG1a3aMgFT-U8HD1VqE6i40ECsIPj4V8ejloYXVHt6xXSTVpCSlgeAGVM_sVDhtTiKbV',
        'test'          =>  'https://discord.com/api/webhooks/934049938873126932/xW-YBQFawAChddAsag87W07TDyM_7EIbct-871YzNCAexy8UVXvalEqsL79zqy1GdBBR'
    ],
    'avatar' => [
        'big'    =>  'https://dexduck-interface-5dpkodrlp-blackrabbit944.vercel.app/img/bot/avatar_424.png',
        'small'  =>  'https://dexduck-interface-5dpkodrlp-blackrabbit944.vercel.app/img/bot/112.png',
    ],
    'bot'   =>  [
        'client_id'     =>  env('DISCORD_APP_ID'),
        'public_key'    =>  env('DISCORD_PUBLIC_KEY'),
        'secret_key'    =>  env('DISCORD_SECRET_KEY'),
        'bot_token'     =>  env('DISCORD_BOT_TOKEN')
    ]
];
