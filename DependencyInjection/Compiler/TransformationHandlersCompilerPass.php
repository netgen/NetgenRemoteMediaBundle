<?php

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

class TransformationHandlersCompilerPass implements CompilerPassInterface
{
    /**
     * Adds all registered transformation handlers to the registry.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $transformationHandlers = $container->findTaggedServiceIds('netgen_remote_media.transformation_handler');

        if (!empty($transformationHandlers) && $container->hasDefinition('netgen_remote_media.handler_registry')) {
            $handlerRegistry = $container->getDefinition('netgen_remote_media.handler_registry');
            foreach ($transformationHandlers as $serviceId => $transformationHandler) {
                if (!isset($transformationHandler[0]['alias'])) {
                    throw new LogicException(
                        'netgen_remote_media.transformation_handler service tag needs an "alias" attribute to identify the handler. None given.'
                    );
                }

                if (!isset($transformationHandler[0]['provider'])) {
                    throw new LogicException(
                        'netgen_remote_media.transformation_handler service tag needs an "provider" attribute to identify which providers it supports. None given.'
                    );
                }

                $handlerRegistry->addMethodCall(
                    'addHandler',
                    array(
                        $transformationHandler[0]['provider'],
                        $transformationHandler[0]['alias'],
                        new Reference($serviceId),
                    )
                );
            }
        }
    }
}
