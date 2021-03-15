<?php

namespace WebEtDesign\MailerBundle\EventListener;

use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClassConstant;
use ReflectionException;
use Symfony\Contracts\EventDispatcher\Event;
use WebEtDesign\MailerBundle\Exception\MailTransportException;
use WebEtDesign\MailerBundle\Model\MailManagerInterface;
use WebEtDesign\MailerBundle\Transport\MailTransportInterface;
use WebEtDesign\MailerBundle\Transport\TransportChain;
use WebEtDesign\MailerBundle\Util\ObjectConverter;

class MailerListener
{
    private MailManagerInterface $manager;

    private TransportChain $transports;

    private LoggerInterface $logger;

    public function __construct(MailManagerInterface $manager, TransportChain $transports, LoggerInterface $logger)
    {
        $this->manager    = $manager;
        $this->transports = $transports;
        $this->logger     = $logger;
    }

    public function __invoke(Event $event)
    {
        $className = get_class($event);
        try {
            $constant = new ReflectionClassConstant($className, 'NAME');
            $name     = $constant->getValue();
        } catch (ReflectionException $e) {
            $name = $className;
        }

        $mails = $this->manager->findByEventName($name);
        if (empty($mails)) {
            return;
        }

        $values = ObjectConverter::convertToArray($event);
        $locale = method_exists($event, 'getLocale') ? $event->getLocale() : null;
        foreach ($mails as $mail) {
            try {
                $res = $this->sendMail($mail, $values, $locale);
                $this->logger->info("Event " . $name . ' catch by mail listener, res = ' . $res);
            } catch (Exception $exception) {
                $this->logger->critical("Event " . $name . ' catch by mail listener, error = ' . $exception->getMessage());
            }
        }
    }

    private function sendMail($mail, $values, $locale)
    {
        $type      = 'twig'; // @TODO replace by mail type transport
        $transport = $this->transports->get($type);
        if (!$transport instanceof MailTransportInterface) {
            throw new MailTransportException('Mail transport not found');
        }

        $to = $mail->getToAsArray();
        if (!$to) {
            throw new MailTransportException('No destination found');
        }

        $mailLocale = $mail->getLocale();
        if ($mailLocale) {
            $locale = ObjectConverter::getValue($mailLocale, $values);
        }

        return $transport->send($mail, $locale, $values, ObjectConverter::getValue($to, $values));
    }
}
