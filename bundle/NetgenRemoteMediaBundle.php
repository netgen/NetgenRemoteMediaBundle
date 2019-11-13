<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle;

use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\TransformationHandlersCompilerPass;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler\XslRegisterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenRemoteMediaBundle extends Bundle
{
    /**
     * Builds the bundle.
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new XslRegisterPass());
        $container->addCompilerPass(new TransformationHandlersCompilerPass());
    }
}
