<?php

namespace WebEtDesign\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class WDMailerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);

        $mailerEvents = $config['events'];
        $container->setParameter('wd_mailer.events', $mailerEvents);

        $container->setParameter('wd_mailer.locales', $config['locales']);
        $container->setParameter('wd_mailer.default_locale', $config['default_locale']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.yaml');
        }

        $service = $container->getDefinition('WebEtDesign\MailerBundle\EventListener\MailerListener');

        foreach ($config['events'] as $key => $event) {
            $service->addTag('kernel.event_listener', ['event' => $key, 'priority' => $event['priority']]);
        }

        $loader->load('doctrine.yaml');
    }
}
