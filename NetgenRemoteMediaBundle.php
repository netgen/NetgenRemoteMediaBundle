<?php

namespace Netgen\Bundle\RemoteMediaBundle;

use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\TransformationHandlersCompilerPass;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\XslRegisterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenRemoteMediaBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new XslRegisterPass());
        $container->addCompilerPass(new TransformationHandlersCompilerPass());
    }
}
