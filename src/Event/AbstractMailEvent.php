<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMailEvent extends Event implements MailEventInterface
{
    private ?string $locale = null;

    private ?array $attachements = [];

    private ?string $replyTo = null;

    abstract public function getEmail(): string;

    /**
     * @deprecated use attachements instead
     */
    public function getFile(): null|array|File
    {
        return $this->attachements;
    }

    /**
     * @param string|null $replyTo
     * @return AbstractMailEvent
     */
    public function setReplyTo(?string $replyTo): AbstractMailEvent
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @param string $locale
     * @return AbstractMailEvent
     */
    public function setLocale(string $locale): AbstractMailEvent
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param array|null $attachements
     * @return AbstractMailEvent
     */
    public function setAttachements(?array $attachements): AbstractMailEvent
    {
        $this->attachements = $attachements;

        return $this;
    }

    public function addAttachement(File $file): AbstractMailEvent
    {
        $this->attachements[] = $file;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAttachements(): ?array
    {
        return $this->attachements;
    }
}
