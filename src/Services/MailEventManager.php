<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Services;

use WebEtDesign\MailerBundle\Attribute\MailEvent;

class MailEventManager
{
    private array $events = [];

    /**
     * @return array<MailEvent>
     */
    public function getEvents(): array
    {
        return array_map(fn($event) => MailEvent::createFromJson($event['config']), $this->events);
    }

    public function getConfig(string $name): ?MailEvent
    {
        return !empty($this->events[$name]) ? MailEvent::createFromJson($this->events[$name]['config']) : null;
    }

    public function getClass(string $name): ?string
    {
        return $this->events[$name]['class'] ?? null;
    }

    /**
     * @param array $events
     * @return MailEventManager
     */
    public function setEvents(array $events): MailEventManager
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @param $class
     * @param $name
     * @param $config
     * @return MailEventManager
     */
    public function addEvent($class, $name, $config): MailEventManager
    {
        if (!array_key_exists($name, $this->events)) {
            $this->events[$name] = [
                'class'  => $class,
                'config' => $config
            ];
        }

        return $this;
    }

}
