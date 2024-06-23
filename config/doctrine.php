<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entity Managers
    |--------------------------------------------------------------------------
    |
    | Configure your Entity Managers here. You can set a different connection
    | and driver per manager and configure events and filters. Change the
    | paths setting to customize where your entities are located.
    |
    */
    'managers' => [
        'comments' => [
            'dev'        => env('APP_ENV') !== 'production',
            'meta'       => env('DOCTRINE_METADATA', 'annotations'),
            'connection' => 'comments',
            'namespaces' => [
                'Skimpy\Comments\Entities'
            ],
            'paths'      => [
                base_path('src/Comments/Entities')
            ],
            'proxies'       => [
                'namespace'     => false,
                'path'          => __DIR__ . '/../cache/doctrine-proxy',
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', true)
            ],
        ],
    ],
];
