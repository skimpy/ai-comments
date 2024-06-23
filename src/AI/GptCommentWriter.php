<?php

declare(strict_types=1);

namespace Skimpy\Comments\AI;

use Skimpy\Comments\AI\GptClient;
use Skimpy\Comments\AI\CommentWriterInterface;

/**
 * Class GptCommentWriter
 *
 * This class is responsible for generating comments using the GPT model.
 * It implements the CommentWriterInterface and utilizes the GptClient to
 * interact with the OpenAI API. The class constructs messages based on
 * provided prompts and content, and handles the response to generate
 * the final comment.
 */
class GptCommentWriter implements CommentWriterInterface
{
    private GptClient $gptClient;

    public function __construct(GptClient $gptClient)
    {
        $this->gptClient = $gptClient;
    }

    public function write(string $prompt, string $content): string
    {
        $systemMessage = [
            'role' => 'system',
            'content' => $prompt
        ];

        $userMessage = [
            'role' => 'user',
            'content' => $content
        ];

        try {
            $response = $this->gptClient->createChat([
                'model' => 'gpt-4o',
                'messages' => [$systemMessage, $userMessage]
            ]);

            return $response['choices'][0]['message']['content'];
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Failed to generate comment: ' . $e->getMessage());
        }
    }
}