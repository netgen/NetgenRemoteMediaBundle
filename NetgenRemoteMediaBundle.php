<?php

namespace Netgen\Bundle\RemoteMediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Netgen\Bundle\MoreBundle\NetgenMoreProjectBundleInterface;
use Keyteq\Bundle\MelkBundle\DependencyInjection\Compiler\XslRegisterPass;

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
    }
}
