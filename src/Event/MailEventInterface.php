<?php
namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;

interface MailEventInterface
{
    public function getEmail(): string;
    public function getLocale(): ?string;
    public function setLocale(string $locale): self;


    /**
     * @return File[]|File|null
     */
    public function getFile(): null|array|File;

    public function getReplyTo(): ?string;
}
