<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle;

use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\TransformationHandlersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenRemoteMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new TransformationHandlersPass(),
        );
    }
}
