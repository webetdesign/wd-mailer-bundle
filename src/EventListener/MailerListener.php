<?php

namespace WebEtDesign\MailerBundle\EventListener;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use ReflectionClassConstant;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;
use WebEtDesign\MailerBundle\Event\EmailSentEvent;
use WebEtDesign\MailerBundle\Event\MailEventInterface;
use WebEtDesign\MailerBundle\Exception\MailTransportException;
use WebEtDesign\MailerBundle\Model\MailManagerInterface;
use WebEtDesign\MailerBundle\Transport\MailTransportInterface;
use WebEtDesign\MailerBundle\Transport\TransportChain;
use WebEtDesign\MailerBundle\Util\ObjectConverter;
use WebEtDesign\MailerBundle\Entity\Mail;

class MailerListener
{
    private MailManagerInterface $manager;

    private TransportChain $transports;

    private LoggerInterface $logger;

    private EventDispatcherInterface $dispatcher;

    private array $constants = [];

    public function __construct(MailManagerInterface $manager, TransportChain $transports, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->manager    = $manager;
        $this->transports = $transports;
        $this->dispatcher = $dispatcher;
        $this->logger     = $logger;
    }

    /**
     * @throws ReflectionException
     * @throws MailTransportException
     */
    public function __invoke(Event $event, $key)
    {
        $className = get_class($event);

        if (!array_key_exists(MailEventInterface::class, class_implements($className))) {
            return;
        }

        try {
            $constant = new ReflectionClassConstant($className, $key);
            $name     = $constant->getValue();
        } catch (ReflectionException $e) {
            $name = $className;
        }

        $mails = $this->manager->findByEventName($name);
        if (empty($mails)) {
            return;
        }

        $values = ObjectConverter::convertToArray($event);

        $locale = method_exists($className, 'getLocale') ? $event->getLocale() : null;

        foreach ($mails as $mail) {
            $type      = 'twig'; // @TODO replace by mail type transport
            $transport = $this->transports->get($type);
            if (!$transport instanceof MailTransportInterface) {
                throw new MailTransportException('Mail transport not found');
            }
            $res = $transport->send($mail, $locale, $values, $this->getRecipients($mail, $values));
            $this->logger->info("Event ".$name.' catch by mail listener, res = '.$res);
            $this->dispatcher->dispatch(new EmailSentEvent($event),EmailSentEvent::NAME);
        }
    }

    /**
     * @param Mail $mail
     * @param $values
     * @return array
     * @throws MailTransportException
     */
    private function getRecipients(Mail $mail, $values): array
    {
        $to = $mail->getToAsArray();
        if (!$to) {
            throw new MailTransportException('No destination found');
        }
        $to = !is_array($to) ? [$to] : $to;
        foreach ($to as $k => $item) {
            if (!preg_match('/^__(.*)__$/', $item, $matches)) {
                continue;
            }

            unset($to[$k]);

            $split = explode('.', $matches[1]);
            $dest  = $values[array_shift($split)] ?? [];


            foreach ($split as $split_item) {
                $method = 'get'.ucfirst($split_item);
                if (!method_exists($dest, $method)) {
                    $dest = null;
                    break;
                }
                $dest = $dest->$method();
            }

            if ($dest) {
                if (is_array($dest)) {
                    $to = [...$to, ...$dest];
                } else {
                    $to[] = $dest;
                }
            }
        }

        return $to;
    }

}
