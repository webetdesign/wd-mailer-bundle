<?php

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMailEvent extends Event implements MailEventInterface
{
    public abstract function getEmail(): string;

    public function getFile(): null|array|File
    {
        return null;
    }

    public function getReplyTo(): ?string
    {
        return null;
    }
}