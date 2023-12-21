<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EmailSentEvent extends Event
{
    public const NAME = 'wd_mailer.email.sent';

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
