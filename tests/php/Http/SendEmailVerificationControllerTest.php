<?php

namespace Tests\Skimpy\Comments\Http;

use Skimpy\Comments\Entities\Email;
use Tests\Skimpy\Comments\TestCase;
use Skimpy\Comments\Mail\MailerInterface;

class SendEmailVerificationControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->bindMockMailer();
    }

    private function bindMockMailer()
    {
        $this->app->bind(MailerInterface::class, function () {
            return new class implements MailerInterface
            {
                public function send(string $to, string $subject, string $body, array $options = []): void
                {
                    // This is a mock implementation that does nothing
                }
            };
        });
    }

    /** @test */
    public function it_sends_an_email_verification_request_to_provided_address()
    {
        $response = $this->call(
            'POST',
            '/api/comments/send-email-verification',
            [
                'name' => 'Bob Commentor',
                'email' => 'jtallant07@gmail.com',
                'entry_uri' => 'should-we-follow-srp-in-controllers'
            ]
        );

        $this->assertEquals(200, $response->status());

        $this->assertEquals('Verification email sent', $response->json('message'));

        $commentsManager = $this->app->make('registry')->getManager('comments');
        $emailsRepository = $commentsManager->getRepository(Email::class);
        $email = $emailsRepository->findOneBy(['email' => 'jtallant07@gmail.com']);

        $this->assertEquals('Bob Commentor', $email->name());
        $this->assertEquals('jtallant07@gmail.com', $email->email());
        $this->assertEquals('should-we-follow-srp-in-controllers', $email->entryUri());
    }

    /** @test */
    public function it_fails_if_missing_name_email_or_entry_uri()
    {
        $testCases = [
            [
                'data' => ['name' => '', 'email' => 'foo@bar.com', 'entry_uri' => 'entry-uri'],
                'error' => 'The name field is required.'
            ],
            [
                'data' => ['name' => 'Bob Commentor', 'email' => '', 'entry_uri' => 'should-we-follow-srp-in-controllers'],
                'error' => 'The email field is required.'
            ],
            [
                'data' => ['name' => 'Bob Commentor', 'email' => 'jtallant07@gmail.com', 'entry_uri' => ''],
                'error' => 'The entry uri field is required.'
            ]
        ];

        foreach ($testCases as $testCase) {
            $response = $this->call('POST', '/api/comments/send-email-verification', $testCase['data']);
            $this->assertEquals(400, $response->status());
            $this->assertEquals($testCase['error'], $response->json('error'));
        }
    }

    /** @test */
    public function it_updates_token_and_expiration_if_email_exists()
    {
        // Create an email entity and persist it to the database
        $commentsManager = $this->app->make('registry')->getManager('comments');
        $emailsRepository = $commentsManager->getRepository(Email::class);

        $existingEmail = new Email('Bob Commentor', 'jtallant07@gmail.com', 'should-we-follow-srp-in-controllers');
        $commentsManager->persist($existingEmail);
        $commentsManager->flush();

        // Assign the old token and expiration to variables before the HTTP call
        $oldToken = $existingEmail->token();
        $oldExpiration = $existingEmail->expiration();

        // Call the endpoint to update the email with the original name
        $response = $this->call(
            'POST',
            '/api/comments/send-email-verification',
            [
                'name' => 'Bob Commentor',
                'email' => 'jtallant07@gmail.com',
                'entry_uri' => 'should-we-follow-srp-in-controllers'
            ]
        );

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Verification email sent', $response->json('message'));

        // Call the endpoint again to update the email with a new name
        $response = $this->call(
            'POST',
            '/api/comments/send-email-verification',
            [
                'name' => 'Alice Commentor',
                'email' => 'jtallant07@gmail.com',
                'entry_uri' => 'should-we-follow-srp-in-controllers'
            ]
        );

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Verification email sent', $response->json('message'));

        // Fetch the updated email entity
        $updatedEmail = $emailsRepository->findOneBy(['email' => 'jtallant07@gmail.com']);

        // Assert that the name has been updated
        $this->assertEquals('Alice Commentor', $updatedEmail->name());

        // Assert that the token and expiration have been updated
        $this->assertNotEquals($oldToken, $updatedEmail->token());
        $this->assertNotEquals($oldExpiration, $updatedEmail->expiration());
        $this->assertEquals($updatedEmail->name(), 'Alice Commentor');
    }
}