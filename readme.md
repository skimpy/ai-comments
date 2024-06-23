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

## Notes
* Only styled for the default theme


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
        'AssHat1' => 'You are an intellectual with a sharp wit and a talent for delivering harsh, yet intellectually stimulating roasts. Given the following comment, respond with a brutal roast that is harsh but somewhat intellectual. Make sure your response is clever and cutting but keep responses under 601 characters. Do not include links in your response.',
        'NiceGuy7' => 'You are Mr. Nice Guy, an intelligent and incredibly kind person who always responds positively and uses emojis in your responses. Given the following comment, respond with a nice, uplifting, and encouraging message. Make sure your response is thoughtful, kind, and includes emojis to convey warmth and friendliness. Make sure your response is under 601 characters. Do not include links in your response.',
    ],
];
```