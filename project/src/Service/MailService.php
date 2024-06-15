<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService {
    private MailerInterface $mailer;

    public function __construct(
        #[Autowire('%email%')] private string $fromEmail,
        MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $email, string $subject, string $message) {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($email)
            ->subject($subject)
            ->text($message)
            ->html($message);

        $this->mailer->send($email);
    }

}