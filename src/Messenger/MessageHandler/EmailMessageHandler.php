<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Messenger\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use WebEtDesign\MailerBundle\Messenger\Message\EmailMessage;
use WebEtDesign\MailerBundle\Services\SymfonyMailerTransport;

#[AsMessageHandler]
class EmailMessageHandler
{

    public function __construct(private SymfonyMailerTransport $mailerTransport)
    {
    }

    public function __invoke(EmailMessage $message): void
    {
        $this->mailerTransport->send($message->getEmail(), $message->getEvent(), $message->getName());
    }
}
