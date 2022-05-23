<?php
namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;

interface MailEventInterface
{
    public function getEmail(): string;

    /**
     * @return File[]|File|null
     */
    public function getFile(): null|array|File;

}