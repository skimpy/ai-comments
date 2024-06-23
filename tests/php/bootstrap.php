<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$site = new \Skimpy\Site(realpath(__DIR__ . '/../app/public'));

/** @var \Laravel\Lumen\Application $app */
$app = $site->bootstrap();

# Register your own service providers here
$app->register(\Skimpy\Comments\Provider\CommentsServiceProvider::class);
$app->register(\Skimpy\Comments\Provider\TwigFunctionsProvider::class);
$app->register(\Skimpy\Comments\Provider\CommentsMailerProvider::class);
$app->register(\Skimpy\Comments\Provider\CommentWriterProvider::class);

# This has to come last because of the greedy rout
$app->register(\Skimpy\Lumen\Providers\SkimpyRouteProvider::class);

return $app;