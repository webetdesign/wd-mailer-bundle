<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Messenger\Message;

use Symfony\Component\Mime\Email;
use WebEtDesign\MailerBundle\Event\MailEventInterface;

readonly class EmailMessage
{
    public function __construct(private Email $email, private MailEventInterface $event, private string $name)
    {
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return MailEventInterface
     */
    public function getEvent(): MailEventInterface
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}
