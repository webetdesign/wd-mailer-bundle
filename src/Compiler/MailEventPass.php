<?php

namespace WebEtDesign\MailerBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebEtDesign\MailerBundle\Services\MailEventManager;

class MailEventPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $eventManager = $container->getDefinition(MailEventManager::class);

        foreach ($container->findTaggedServiceIds('wd_mailer.event') as $class => $tags) {
            foreach ($tags as $tag) {
                $config = json_decode($tag['event'], true);
                $eventManager->addMethodCall('addEvent', [$config['name'], $config['label'], $class]);
            }
        }
    }
}