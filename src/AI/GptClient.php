<?php

declare(strict_types=1);

namespace Skimpy\Comments\AI;

use OpenAI\Client;

/**
 * Class GptClient
 *
 * This class represents a GPT client for creating chat sessions.
 * This class is used for testing purposes due to the OpenAI\Client class being final.
 */
class GptClient
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a chat session with the given parameters.
     *
     * @param array{
     *     model: string,
     *     messages: array<array{role: string, content: string}>
     * } $params The parameters for creating the chat session.
     * @return \ArrayAccess The response from the chat creation.
     */
    public function createChat(array $params): \ArrayAccess
    {
        return $this->client->chat()->create($params);
    }
}