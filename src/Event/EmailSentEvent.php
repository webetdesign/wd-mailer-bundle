<?php

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EmailSentEvent extends Event
{
    public const NAME = 'email.sent';

    private Event $previousEvent;

    public function __construct(Event $previousEvent)
    {
        $this->previousEvent = $previousEvent;
    }

    /**
     * @return Event
     */
    public function getPreviousEvent(): Event
    {
        return $this->previousEvent;
    }
}