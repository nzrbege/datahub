<?php

return [
    'default'   => [
        'length'    => 6,
        'width'     => 150,
        'height'    => 46,
        'quality'   => 90,
        'math'      => false,
        'expire'    => 60,
        'encrypt'   => false,
        'lines'     => 3,
        'bgImage'   => false,
        'bgColor'   => '#ffffff',
        'fontColors'=> ['#1e3a5f', '#c8102e', '#1a7431'],
        'contrast'  => -5,
    ],

    'flat' => [
        'length'    => 6,
        'width'     => 160,
        'height'    => 46,
        'quality'   => 90,
        'lines'     => 6,
        'bgImage'   => false,
        'bgColor'   => '#ecf0f1',
        'fontColors'=> ['#2c3e50', '#c0392b'],
        'contrast'  => -5,
    ],

    'inverse' => [
        'length'    => 5,
        'width'     => 120,
        'height'    => 36,
        'quality'   => 90,
        'sensitive' => true,
        'angle'     => 12,
        'sharpen'   => 10,
        'blur'      => 2,
        'invert'    => true,
        'contrast'  => -5,
    ],

    'math' => [
        'length'    => 9,
        'width'     => 120,
        'height'    => 36,
        'quality'   => 90,
        'math'      => true,
    ],
];
