<?php

declare(strict_types=1);

namespace Skimpy\Comments\AI;

/**
 * Interface CommentWriterInterface
 *
 * This interface defines a contract for writing comments based on a given prompt and content.
 * Implementations of this interface should provide the logic to generate a comment string.
 */
interface CommentWriterInterface
{
    /**
     * Write a comment based on the provided prompt and content.
     *
     * @param string $prompt The prompt to guide the comment writing.
     * @param string $content The content to be included in the comment.
     * @return string The generated comment.
     */
    public function write(string $prompt, string $content): string;
}
