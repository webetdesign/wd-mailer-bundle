<?php

namespace WebEtDesign\MailerBundle\Services;

class MailEventManager
{
    private array $events = [];

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getConfig(string $name)
    {
        return $this->events[$name] ?? null;
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
     * @param $name
     * @param $label
     * @param $spool
     * @param $class
     * @return MailEventManager
     */
    public function addEvent ($name, $label, $spool, $class): MailEventManager
    {
        if (!array_key_exists($name, $this->events)){
            $this->events[$name] = [
                'label' => $label,
                'spool' => $spool,
                'class' => $class
            ];
        }

        return $this;
    }

}
