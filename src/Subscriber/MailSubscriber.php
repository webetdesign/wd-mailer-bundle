<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Services\MailEventManager;

#[AutoconfigureTag(name: 'doctrine.event_subscriber')]
class MailSubscriber implements EventSubscriber
{

    public function __construct(private MailEventManager $manager)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
        ];
    }

    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Mail) {
            return;
        }

        $config = $this->manager->getConfig($entity->getEvent());
        $entity->setName($config['label']);
    }
}
