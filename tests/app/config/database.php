<?php

return [
    'connections' => [
        'comments' => [
            'paths' => [
                __DIR__ . '/../../../src/Entities'
            ],
            'dev' => env('APP_ENV') !== 'production',
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/comments.sqlite',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],
];