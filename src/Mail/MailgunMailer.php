<?php

namespace Skimpy\Comments\Mail;

use Mailgun\Mailgun;

class MailgunMailer implements MailerInterface
{
    private Mailgun $mailgun;
    private string $domain;

    public function __construct(Mailgun $mailgun, string $domain)
    {
        $this->mailgun = $mailgun;
        $this->domain = $domain;
    }

    public function send(string $to, string $subject, string $body, array $options = []): void
    {
        $this->mailgun->messages()->send($this->domain, [
            'from'    => $options['from'] ?? 'no-reply@' . $this->domain,
            'to'      => $to,
            'subject' => $subject,
            'text'    => $body,
        ]);
    }
}