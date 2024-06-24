<?php

declare(strict_types=1);

namespace Skimpy\Comments\Provider;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\ServiceProvider;

class CommentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigs();
        $this->setupCommentsDatabase();
        $this->copyTemplates();
    }

    public function boot(): void
    {
        $this->defineRoutes();
    }

    private function mergeConfigs(): void
    {
        $this->loadAndMergeConfigFrom(base_path('config/database.php'), 'database');
        $this->loadAndMergeConfigFrom(base_path('config/doctrine.php'), 'doctrine');
        $this->loadAndMergeConfigFrom(base_path('config/comments.php'), 'comments');
    }

    private function loadAndMergeConfigFrom(string $path, string $key): void
    {
        $config = $this->app->make('config');
        $original = $config->get($key, []);
        $values = require $path;
        $config->set($key, array_merge_recursive($original, $values));
    }

    private function setupCommentsDatabase(): void
    {
        if (php_sapi_name() !== 'cli' && ! file_exists(base_path('database/comments.sqlite'))) {
            touch(base_path('database/comments.sqlite'));
        }

        $entityManager = $this->app->make('registry')->getManager('comments');
        $classes = $entityManager->getMetadataFactory()->getAllMetadata();

        $createIfNotExists = true;
        (new SchemaTool($entityManager))->updateSchema($classes, $createIfNotExists);
    }

    private function defineRoutes(): void
    {
        $router = $this->app->make('router');

        $router->group(['prefix' => 'api/comments'], function () use ($router) {
            $router->get('/', 'Skimpy\Comments\Http\CommentsController@index');
            $router->post('/', 'Skimpy\Comments\Http\CommentsController@store');
            $router->post('send-email-verification', 'Skimpy\Comments\Http\SendEmailVerificationController@store');
            $router->post('verify-comments-token', 'Skimpy\Comments\Http\VerifyCommentsTokenController@show');
        });

        $router->group(['prefix' => 'comments/email-verification'], function () use ($router) {
            $router->get('/', 'Skimpy\Comments\Http\EmailVerificationController@show');
        });
    }

    private function copyTemplates(): void
    {
        $source = __DIR__ . '/../../templates/comments';
        $destination = base_path('site/templates/packages/comments');

        $finder = new Finder();
        $finder->in([$source]);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        foreach ($finder as $file) {
            $sourceFile = $file->getRealPath();
            $targetFile = $destination . '/' . $file->getRelativePathname();

            if ($file->isDir() && !is_dir($targetFile)) {
                mkdir($targetFile, 0755, true);
                continue;
            }

            if (false === $file->isDir()) {
                copy($sourceFile, $targetFile);
            }
        }
    }
}