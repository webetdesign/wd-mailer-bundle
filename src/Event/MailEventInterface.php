<?php
namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;

interface MailEventInterface
{
    public function getEmail(): string;
    public function getFile(): ?File;

}