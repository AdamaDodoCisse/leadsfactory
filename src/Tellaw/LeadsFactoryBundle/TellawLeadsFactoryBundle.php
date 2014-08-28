<?php

namespace Tellaw\LeadsFactoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tellaw\LeadsFactoryBundle\CompilerPass\ExportCompilerPass;

class TellawLeadsFactoryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExportCompilerPass());
    }

}
