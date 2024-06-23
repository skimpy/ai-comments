<?php

declare(strict_types=1);

namespace Skimpy\Comments\Provider;

use OpenAI;
use Skimpy\Comments\AI\GptClient;
use Illuminate\Support\ServiceProvider;
use Skimpy\Comments\AI\GptCommentWriter;
use Skimpy\Comments\AI\CommentWriterInterface;
use Skimpy\Comments\AI\CreateAgentEntryComments;
use Skimpy\Comments\AI\CreateAgentCommentReplies;

/**
 * Class CommentWriterProvider
 *
 * This service provider is responsible for registering and binding various
 * services related to AI-driven comment writing within the application.
 * It sets up singleton instances for GptCommentWriter, CreateAgentCommentReplies,
 * and CreateAgentEntryComments, and binds the CommentWriterInterface to the
 * GptCommentWriter implementation. Additionally, it registers console commands
 * for creating agent comments and replies.
 */
class CommentWriterProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GptCommentWriter::class, function () {
            $apiKey = config('comments.openai_api_key');
            $client = new GptClient(OpenAI::client($apiKey));
            return new GptCommentWriter($client);
        });

        $this->app->bind(CommentWriterInterface::class, GptCommentWriter::class);

        $this->app->singleton(CreateAgentCommentReplies::class, function ($app) {
            return new CreateAgentCommentReplies($app->make('registry'), config('comments.prompts'));
        });

        $this->app->singleton(CreateAgentEntryComments::class, function ($app) {
            return new CreateAgentEntryComments($app->make('registry'), config('comments.prompts'));
        });

        $this->commands([
            CreateAgentCommentReplies::class,
            CreateAgentEntryComments::class,
        ]);
    }
}