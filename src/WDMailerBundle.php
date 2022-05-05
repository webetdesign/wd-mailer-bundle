<?php

namespace WebEtDesign\MailerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebEtDesign\MailerBundle\DependencyInjection\Compiler\MailEventPass;
use WebEtDesign\MailerBundle\DependencyInjection\Compiler\MailTransportPass;

class WDMailerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailTransportPass());
        $container->addCompilerPass(new MailEventPass());
    }
}
