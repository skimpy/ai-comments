# AI Comments

Work in progress. Do not install yet.

## Installation
* Automatic
    - `database/comments.sqlite` is created if it does not exist
    - `site/templates/packages/comments` folder is created
    - `site/templates/packages/comments/partials/child-comments.twig` is created
    - `site/templates/packages/comments/partials/comments-form.twig` is created
    - `site/templates/packages/comments/email-verified.twig` is created
    - `site/templates/packages/comments/invalid-token.twig` is created
* Create config files
    - `config/comments.php`
* Update config files
    - `config/database.php`
    - `config/doctrine.php`
* Add assets
    - `comments.scss`
    - `comments.js`
* Add config files listed below
* Update .env file
* Update .gitignore to ignore database/cache/doctrine-proxy


## Installation - Register Providers
```php
// bootstrap/app.php
require_once __DIR__ . '/../vendor/autoload.php';

$site = new \Skimpy\Site(__DIR__);

/** @var \Laravel\Lumen\Application $app */
$app = $site->bootstrap();

# Register your own service providers here
$app->register(\Skimpy\Comments\Provider\CommentsServiceProvider::class);
$app->register(\Skimpy\Comments\Provider\TwigFunctionsProvider::class);
$app->register(\Skimpy\Comments\Provider\CommentsMailerProvider::class);
$app->register(\Skimpy\Comments\Provider\CommentWriterProvider::class);

# This has to come last because of the greedy routes
$app->register(\Skimpy\Lumen\Providers\SkimpyRouteProvider::class);

return $app;
```

## Notes
* Only styled for the default theme


#### config/database.php
```php
<?php

return [
    'connections' => [
        'comments' => [
            'paths' => [
                base_path('vendor/skimpy/ai-comments/src/Entities')
            ],
            'dev' => env('APP_ENV') !== 'production',
            'driver' => 'sqlite',
            'database' => env('COMMENTS_DB_FILE_PATH', base_path('database/comments.sqlite')),
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],
];
```

#### config/doctrine.php
```php
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
                base_path('vendor/skimpy/ai-comments/src/Entities')
            ],
            'proxies'       => [
                'namespace'     => false,
                'path'          => base_path('database/cache/doctrine-proxy'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', true)
            ],
        ],
    ],
];
```

#### config/comments.php
```php
<?php

return [
    'site_owner_secret' => env('COMMENTS_SITE_OWNER_SECRET', null),
    'site_owner_name' => env('COMMENTS_SITE_OWNER_NAME', 'Author'),
    'mail_domain' => env('COMMENTS_MAIL_DOMAIN', 'example.com'),
    'mail_api_key' => env('COMMENTS_MAIL_API_KEY', null),
    'mail_from' => env('COMMENTS_MAIL_FROM', 'no-reply@example.com'),
    'mail_subject' => 'Please verify your email address',
    'openai_api_key' => env('COMMENTS_OPENAI_API_KEY', null),
    'prompts' => [
        // Each prompt personality will generate one comment for each new entry.
        // For comment replies - a random personality will be selected. Only comments of a certain length are responded to.
        'AssHat1' => 'You are an intellectual with a sharp wit and a talent for delivering harsh, yet intellectually stimulating roasts. Given the following comment, respond with a brutal roast that is harsh but intellectual. Make sure your response is clever and cutting but keep responses under 601 characters. Do not include links in your response.',
        'NiceGuy7' => 'You are Mr. Nice Guy, an intelligent and incredibly kind person who always responds positively and uses emojis in your responses. Given the following comment, respond with a nice, uplifting, and encouraging message. Make sure your response is thoughtful, kind, and includes emojis to convey warmth and friendliness but make sure your response is under 601 characters. Do not include links in your response.',
    ],
];
```

#### .env
```
COMMENTS_SITE_OWNER_SECRET="secretkey"
COMMENTS_SITE_OWNER_NAME="Your Name"
COMMENTS_MAIL_DOMAIN="mailgun_domain"
COMMENTS_MAIL_API_KEY="mailgun_api_key"
COMMENTS_MAIL_FROM="verify@your-domain.com"
COMMENTS_OPENAI_API_KEY=""
```

#### Env Explanation
* COMMENTS_SITE_OWNER_SECRET
    - Allows you to identify yourself as the author when making a comment
    - Instead of typing your name, you type your key and the comment uses your image
    - Your COMMENTS_SITE_OWNER_NAME is used at the comment author name
    - Your image should be located at (foo path)