<?php

namespace WebEtDesign\MailerBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebEtDesign\MailerBundle\Attribute\MailEvent;
use WebEtDesign\MailerBundle\Compiler\MailEventPass;
use WebEtDesign\MailerBundle\Compiler\MailTransportPass;

class WDMailerBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailTransportPass());
        $container->addCompilerPass(new MailEventPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $container->import('../config/doctrine.yaml');

        $container->parameters()->set('wd_mailer.locales', $config['locales']);
        $container->parameters()->set('wd_mailer.default_locale', $config['default_locale']);

        $bundles = $builder->getParameter('kernel.bundles');
        if (isset($bundles['SonataAdminBundle'])) {
            $container->import('../config/admin.yaml');
        }

        $mailerListener = $builder->getDefinition('WebEtDesign\MailerBundle\EventListener\MailerListener');

        $builder->registerAttributeForAutoconfiguration(MailEvent::class,
            static function (ChildDefinition $definition, MailEvent $attribute) use ($mailerListener) {
                $mailerListener->addTag('kernel.event_listener', ['event' => $attribute->name, 'priority' => $attribute->priority]);
                $definition->addTag('wd_mailer.event', ['event' => json_encode($attribute)]);
            }
        );
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->children()
                ->booleanNode('async')->defaultFalse()
            ->end()
            ->scalarNode('default_locale')->isRequired()->end()
                ->arrayNode('locales')
                ->scalarPrototype()->isRequired()->end()
            ->end()
            ->arrayNode('events')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('label')->end()
                        ->scalarNode('priority')->defaultValue(0)->end()
                        ->scalarNode('constant')->defaultValue('NAME')->end()
                    ->end()
                ->end()
            ->end();
    }
}