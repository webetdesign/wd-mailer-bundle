<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WebEtDesign\MailerBundle\Attribute\MailEvent;
use WebEtDesign\MailerBundle\Compiler\MailEventPass;

class WDMailerBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailEventPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $container->import('../config/doctrine.yaml');

        $container->parameters()->set('wd_mailer.locales', $config['locales']);
        $container->parameters()->set('wd_mailer.default_locale', $config['default_locale']);

        $container->parameters()->set('wd_mailer.spool.batch_size', $config['spool']['batch_size']);
        $container->parameters()->set('wd_mailer.spool.batch_interval_second', $config['spool']['batch_interval_second']);
        $container->parameters()->set('wd_mailer.auto_configure_events', $config['auto_configure_events']);

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
            ->arrayNode('spool')->addDefaultsIfNotSet()
            ->children()
                ->integerNode('batch_size')->defaultValue(100)->end()
                ->integerNode('batch_interval_second')->defaultValue(15)->end()
            ->end()
            ->end()
            ->scalarNode('default_locale')->isRequired()->end()
                ->arrayNode('locales')
                ->scalarPrototype()->isRequired()->end()
            ->end()
            ->arrayNode('auto_configure_events')
                ->useAttributeAsKey('event')
                ->arrayPrototype()->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')->isRequired()->end()
                        ->scalarNode('from_name')->defaultNull()->end()
                        ->scalarNode('to')->isRequired()->end()
                        ->scalarNode('reply_to')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
