<?php

namespace WebEtDesign\MailerBundle\EventListener;

use ReflectionClassConstant;
use ReflectionException;
use Symfony\Contracts\EventDispatcher\Event;
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

    public function __construct(MailManagerInterface $manager, TransportChain $transports)
    {
        $this->manager    = $manager;
        $this->transports = $transports;
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
        foreach ($mails as $mail) {
            $type      = 'twig'; // @TODO replace by mail type transport
            $transport = $this->transports->get($type);
            if (!$transport instanceof MailTransportInterface) {
                throw new MailTransportException('Mail transport not found');
            }

            $transport->send($mail, $values, $this->getRecipients($mail, $values));
        }
    }

    private function getRecipients(Mail $mail, $values)
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


            foreach ($split as $item) {
                $method = 'get' . ucfirst($item);
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
