<?php

namespace LeadsFactoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LeadsFactoryBundle\CompilerPass\ExportCompilerPass;
use LeadsFactoryBundle\CompilerPass\SchedulerCompilerPass;

class LeadsFactoryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        //$container->addCompilerPass(new ExportCompilerPass());
        //$container->addCompilerPass(new SchedulerCompilerPass());
    }

}
