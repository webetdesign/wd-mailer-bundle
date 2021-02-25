<?php

namespace WebEtDesign\MailerBundle\Transport;

use WebEtDesign\MailerBundle\Entity\Mail;

interface MailTransportInterface
{

    public function send(Mail $mail, $locale = null, $values = null, $to = null);

}
