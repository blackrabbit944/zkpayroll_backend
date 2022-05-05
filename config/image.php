<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => 'imagick',


    'template'  =>  [
        'avatar'    =>  [
            'min_width'     =>  200,
            'min_height'    =>  200,
            'resize'        =>  ['origin','avatar']
        ],
        'cover'    =>  [
            'min_width'     =>  1024,
            'min_height'    =>  200,
            'resize'        =>  ['origin','cover']
        ],
        'post_image'    =>  [
            'min_width'     =>  10,
            'min_height'    =>  10,
            'resize'        =>  ['origin','post_image']
        ],
    ],

    'resize'    =>  [
        'origin'    =>  [
            'max_width'     =>  1200,
            'max_height'    =>  2400,
            'resize_type'   =>  'max',
        ],
        'avatar'    =>  [
            'width'         =>  200,
            'height'        =>  200,
            'resize_type'   =>  'crop',
        ],
        'cover'    =>  [
            'width'         =>  1024,
            'height'        =>  200,
            'resize_type'   =>  'crop',
        ],
        'post_image'        =>  [
            'width'         =>  900,
            'resize_type'   =>  'max_width',
        ],
    ],

];
