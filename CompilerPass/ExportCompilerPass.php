<?php

namespace Tellaw\LeadsFactoryBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExportCompilerPass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('export_utils')) {
            return;
        }

        $definition = $container->getDefinition('export_utils');
        $taggedServices = $container->findTaggedServiceIds('export.method');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addMethod',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }


        if (!$container->hasDefinition('preferences_utils')) {
            return;
        }

        $definition = $container->getDefinition('preferences_utils');
        $taggedServices = $container->findTaggedServiceIds('preference.key');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addMethod',
                    array(new Reference($id))
                );
            }
        }

    }
} 