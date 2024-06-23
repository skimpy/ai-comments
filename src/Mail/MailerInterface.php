<?php

namespace Skimpy\Comments\Mail;

interface MailerInterface
{
    /**
     * Send an email.
     *
     * @param string $to The recipient email address.
     * @param string $subject The subject of the email.
     * @param string $body The body content of the email.
     * @param array<string, mixed> $options Additional options for the email.
     * @return void
     */
    public function send(string $to, string $subject, string $body, array $options = []): void;
}
