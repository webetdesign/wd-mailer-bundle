<?php
declare(strict_types=1);

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
     * @param $class
     * @param $config
     * @return MailEventManager
     */
    public function addEvent($class, $config): MailEventManager
    {
        if (!array_key_exists($config['name'], $this->events)) {
            $this->events[$config['name']] = [
                'class'   => $class,
                'label'   => $config['label'],
                'spool'   => $config['spool'],
                'subject' => $config['subject'],
                'html'    => $config['templateHtml'],
                'text'    => $config['templateText'],
            ];
        }

        return $this;
    }

}
