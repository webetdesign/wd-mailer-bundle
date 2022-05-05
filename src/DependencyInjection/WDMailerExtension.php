<?php

namespace WebEtDesign\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use WebEtDesign\CmsBundle\Attribute\AsCmsBlock;
use WebEtDesign\MailerBundle\Attribute\MailEvent;

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

        $container->setParameter('wd_mailer.locales', $config['locales']);
        $container->setParameter('wd_mailer.default_locale', $config['default_locale']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.yaml');
        }

        $mailerListener = $container->getDefinition('WebEtDesign\MailerBundle\EventListener\MailerListener');

        if (method_exists($container, 'registerAttributeForAutoconfiguration')) {
            $container->registerAttributeForAutoconfiguration(MailEvent::class,
                static function (ChildDefinition $definition, MailEvent $attribute) use ($mailerListener) {
                    $mailerListener->addTag('kernel.event_listener', ['event' => $attribute->name, 'priority' => $attribute->priority]);
                    $definition->addTag('wd_mailer.event', ['event' => json_encode($attribute)]);
                }
            );
        }

        $loader->load('doctrine.yaml');
    }
}
