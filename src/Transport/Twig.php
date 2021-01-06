<?php

namespace WebEtDesign\MailerBundle\Transport;

use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Exception\MailTransportException;

class Twig implements MailTransportInterface
{
    private Environment $twig;
    private Swift_Mailer $mailer;

    public function __construct(Environment $twig, Swift_Mailer $mailer)
    {
        $this->twig   = $twig;
        $this->mailer = $mailer;
    }

    public function send(Mail $mail, $values = null, $to = null): void
    {
        if (!$values) {
            $this->twig->disableStrictVariables();
        }

        $tpl     = $this->twig->createTemplate($mail->getContentHtml());
        $content = $tpl->render($values ?? []);

        $message = (new Swift_Message($mail->getTitle()))
            ->setFrom($mail->getFrom())
            ->setTo($to)
            ->setBody(
                $content,
                'text/html'
            );

        $this->mailer->send($message);
    }
}
