<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CachePoolPass implements CompilerPassInterface
{
    /**
     * Sets correct adapter service and provider for cache pool.
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('netgen_remote_media.cache.pool_name')) {
            return;
        }

        $container->setDefinition(
            'netgen_remote_media.cache.pool',
            $container->findDefinition(
                $container->getParameter('netgen_remote_media.cache.pool_name'),
            ),
        );
    }
}
