<?php

namespace Tests\Skimpy\Comments\Http;

use Tests\Skimpy\Comments\TestCase;
use Skimpy\Comments\Entities\Comment;

class CommentsControllerTest extends TestCase
{
    /** @test */
    public function index_of_entry_uri_returns_entry_comment_json()
    {
        $commentsData = [
            [
                'entry_uri' => 'example-uri',
                'author' => 'John Doe',
                'content' => 'This is a sample comment.'
            ],
            [
                'entry_uri' => 'example-uri',
                'author' => 'Jane Smith',
                'content' => 'This is another sample comment.'
            ]
        ];

        foreach ($commentsData as $data) {
            $comment = new Comment(
                $data['entry_uri'],
                $data['author'],
                $data['content']
            );
            $this->commentsManager->persist($comment);
        }

        $this->commentsManager->flush();

        $response = $this->get('/api/comments?entry_uri=example-uri');

        $response->seeStatusCode(200);

        $response->seeJson($commentsData[0]);
        $response->seeJson($commentsData[1]);
    }

    /** @test */
    public function validation_fails_if_missing_field()
    {
        $data = [
            'entry_uri' => 'example-uri',
            'author' => '',
            'content' => 'This is a sample comment.'
        ];

        $response = $this->post('/api/comments', $data);
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'message',
            'errors' => [
                'author'
            ]
        ]);
    }

    /** @test */
    public function validation_fails_if_content_is_too_long()
    {
        $data = [
            'entry_uri' => 'example-uri',
            'author' => 'John Doe',
            'content' => str_repeat('a', 2401) // 2401 characters long
        ];

        $response = $this->post('/api/comments', $data);
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'message',
            'errors' => [
                'content'
            ]
        ]);
    }

    /** @test */
    public function comment_is_successfully_created()
    {
        $data = [
            'entry_uri' => 'example-uri',
            'author' => 'John Doe',
            'content' => 'This is a sample comment.'
        ];

        $response = $this->post('/api/comments', $data);
        $response->seeStatusCode(201);

        $response->seeJsonStructure([
            'message',
            'data' => [
                'id',
                'entry_uri',
                'author',
                'content',
                'created_at'
            ]
        ]);
    }

    /** @test */
    public function author_name_is_correct_if_name_matches_author_secret_key()
    {
        $authorSecret = 'authorsecret';

        config(['comments.site_owner_secret' => $authorSecret]);
        config(['comments.site_owner_name' => 'Justin Tallant']);

        $data = [
            'entry_uri' => 'example-uri',
            'author' => $authorSecret,
            'content' => 'This is a sample comment.'
        ];

        $response = $this->post('/api/comments', $data);

        $response->seeJsonContains([
            'author' => 'Justin Tallant',
            'content' => 'This is a sample comment.',
        ]);
    }
}
