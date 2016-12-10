<?php

namespace LeadsFactoryBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SchedulerCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheduler.utils')) {
            return;
        }

        $definition = $container->getDefinition('scheduler.utils');
        $taggedServices = $container->findTaggedServiceIds('scheduled.job');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addScheduledJob',
                    array($id)
                );
            }
        }
    }
} 
