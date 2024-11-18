<?php

namespace App\Utilities;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailSender
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    /**
     * @var string
     */
    private string $to;
    /**
     * @var string
     */
    private string $subject;
    /**
     * @var string
     */
    private string $body;

    /**
     * @param MailerInterface $mailer
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    public function __construct(MailerInterface $mailer, string $to, string $subject, string $body)
    {
        $this->mailer = $mailer;
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(): void
    {
        $email = (new Email())
            ->from('')
            ->to($this->to)
            ->subject($this->subject)
            ->html($this->body);

        $this->mailer->send($email);
    }
}
