<?php

namespace Tests\Skimpy\Comments\Provider;

use Tests\Skimpy\Comments\TestCase;
use Skimpy\Comments\Mail\MailgunMailer;
use Skimpy\Comments\Mail\MailerInterface;

class CommentsMailerProviderTest extends TestCase
{
    /** @test */
    public function mailgun_mailer_is_bound_to_mailer_interface()
    {
        config(['comments.mail_api_key' => 'test']);

        $this->assertInstanceOf(
            MailgunMailer::class,
            $this->app->make(MailerInterface::class)
        );
    }
}