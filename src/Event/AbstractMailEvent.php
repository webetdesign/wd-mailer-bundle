<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMailEvent extends Event implements MailEventInterface
{
    private ?string $locale = null;

    abstract public function getEmail(): string;

    public function getFile(): null|array|File
    {
        return null;
    }

    public function getReplyTo(): ?string
    {
        return null;
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
}
