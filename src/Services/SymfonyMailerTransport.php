<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Services;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebEtDesign\MailerBundle\Event\EmailSentEvent;
use WebEtDesign\MailerBundle\Event\MailEventInterface;

readonly class SymfonyMailerTransport
{
    public function __construct(
        private MailerInterface          $mailer,
        private LoggerInterface          $wdMailerLogger,
        private SerializerInterface      $serializer,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public function send(Email $email, MailEventInterface $event, string $eventName): void
    {
        try {
            $this->mailer->send($email);
            $this->wdMailerLogger->info("WD_MAILER", $this->logData($eventName, $email));
        } catch (TransportExceptionInterface $e) {
            $this->wdMailerLogger->critical("WD_MAILER", $this->logData($eventName, $email, $e));
        }

        $this->eventDispatcher->dispatch(new EmailSentEvent($event), EmailSentEvent::NAME);
    }

    private function logData(string $eventName, Email $email, Exception $exception = null): array
    {
        $data = [
            'event_name' => $eventName,
            'satuts'     => $exception === null ? 'sent' : 'error',
            'from'       => $this->serializer->normalize($email->getFrom()),
            'to'         => $this->serializer->normalize($email->getTo()),
        ];

        if ($exception) {
            $data['exception'] = $exception;
        }

        return $data;
    }
}
