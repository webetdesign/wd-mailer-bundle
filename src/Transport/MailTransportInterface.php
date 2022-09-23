<?php

namespace WebEtDesign\MailerBundle\Transport;

use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Event\MailEventInterface;

interface MailTransportInterface
{

    public function send(Mail $mail, MailEventInterface $event, $locale = null, $values = null, $to = null);

}
