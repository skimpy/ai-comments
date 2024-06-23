<?php

namespace Skimpy\Comments\Provider;

use Mailgun\Mailgun;
use Illuminate\Support\ServiceProvider;
use Skimpy\Comments\Mail\MailgunMailer;
use Skimpy\Comments\Mail\MailerInterface;

class CommentsMailerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(MailerInterface::class, function ($app) {

            $mailgun = Mailgun::create(config('comments.mail_api_key'));

            return new MailgunMailer(
                $mailgun, config('comments.mail_domain')
            );
        });
    }
}