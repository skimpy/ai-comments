<?php

declare(strict_types=1);

namespace Skimpy\Comments\Provider;

use Illuminate\Support\ServiceProvider;
use Skimpy\Comments\Entities\Comment;
use Skimpy\Comments\CommentViewDecorator;

class TwigFunctionsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->addCommentsTwigFunctions();
        $this->loadCommentTemplates();
    }

    private function addCommentsTwigFunctions(): void
    {
        $twig = $this->app->get('twig');

        $commentsRepo = $this->app->make('registry')
            ->getManager('comments')
            ->getRepository(Comment::class);

        $config = $this->app->make('config');
        $siteOwnerSecret = $config->get('comments.site_owner_secret');
        $siteOwnerName = $config->get('comments.site_owner_name');

        $getComments = function ($entryUri) use ($commentsRepo, $siteOwnerSecret, $siteOwnerName) {
            $comments = $commentsRepo->findBy([
                'entryUri' => $entryUri,
                'repliesTo' => null,
            ], ['createdAt' => 'DESC']);

            return array_map(function ($comment) use ($siteOwnerSecret, $siteOwnerName) {
                return new CommentViewDecorator($comment, $siteOwnerSecret, $siteOwnerName);
            }, $comments);
        };

        $getChildren = function ($repliesToId) use ($commentsRepo, $siteOwnerSecret, $siteOwnerName) {
            $comments =  $commentsRepo->findBy(['repliesTo' => $repliesToId,], ['createdAt' => 'ASC']);

            return array_map(function ($comment) use ($siteOwnerSecret, $siteOwnerName) {
                return new CommentViewDecorator($comment, $siteOwnerSecret, $siteOwnerName);
            }, $comments);
        };

        $twig->addFunction(new \Twig\TwigFunction('comments', $getComments));
        $twig->addFunction(new \Twig\TwigFunction('childComments', $getChildren));
    }

    private function loadCommentTemplates(): void
    {
        $viewFactory = $this->app->get('view');
        $viewFactory->addLocation(base_path('src/Comments/templates'));
    }
}