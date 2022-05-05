<?php

namespace WebEtDesign\MailerBundle\DependencyInjection\Compiler;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use WebEtDesign\MailerBundle\Transport\TransportChain;

class MailTransportPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(TransportChain::class)) {
            return;
        }

        $definition = $container->findDefinition(TransportChain::class);

        $taggedServices = $container->findTaggedServiceIds('mailer.transport');

        foreach ($taggedServices as $id => $tags) {
            $alias = strtolower((new ReflectionClass($id))->getShortName());
            $definition->addMethodCall('addTransport', [new Reference($id), $alias]);
        }
    }
}