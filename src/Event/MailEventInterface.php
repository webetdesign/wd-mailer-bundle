<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;

interface MailEventInterface
{
    public function getEmail(): string;

    public function getLocale(): ?string;

    public function setLocale(string $locale): self;

    /**
     * @return File[]|File|null
     * @deprecated use attachements instead
     */
    public function getFile(): null|array|File;

    public function getReplyTo(): ?string;

    public function setReplyTo(?string $replyTo): AbstractMailEvent;

    public function setAttachements(?array $attachements): AbstractMailEvent;

    public function addAttachement(File $file): AbstractMailEvent;

    public function getAttachements(): ?array;
}
