<?php

namespace WebEtDesign\MailerBundle\Model;

use WebEtDesign\MailerBundle\Entity\Mail;

interface MailManagerInterface
{

    /**
     * @param $name
     * @return Mail[]
     */
    public function findByEventName($name): array;
}