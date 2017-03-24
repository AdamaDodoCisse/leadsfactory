<?php

namespace Tellaw\LeadsFactoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tellaw\LeadsFactoryBundle\CompilerPass\ExportCompilerPass;
use Tellaw\LeadsFactoryBundle\CompilerPass\SchedulerCompilerPass;

class TellawLeadsFactoryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExportCompilerPass());
        $container->addCompilerPass(new SchedulerCompilerPass());
    }

}
