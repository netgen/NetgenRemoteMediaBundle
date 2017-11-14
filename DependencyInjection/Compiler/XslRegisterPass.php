<?php

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XslRegisterPass implements CompilerPassInterface
{
    /**
     * Compiler pass to register ezxml_ngremotemedia.xsl as custom XSL stylesheet for
     * XmlText field type
     *
     * Avoids having it in %kernel.root_dir%/Resources folder
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $scopes = array_merge(
            array(ConfigResolver::SCOPE_DEFAULT),
            $container->getParameter('ezpublish.siteaccess.list')
        );

        foreach ($scopes as $scope) {
            if (!$container->hasParameter("ezsettings.$scope.fieldtypes.ezxml.custom_xsl")) {
                continue;
            }

            $xslConfig = $container->getParameter("ezsettings.$scope.fieldtypes.ezxml.custom_xsl");
            $xslConfig[] = array('path' => __DIR__ . '/../../Resources/xsl/ezxml_ngremotemedia.xsl', 'priority' => 5000);
            $container->setParameter("ezsettings.$scope.fieldtypes.ezxml.custom_xsl", $xslConfig);
        }
    }
}
