<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function array_merge;
use function sprintf;

class XslRegisterPass implements CompilerPassInterface
{
    /**
     * Compiler pass to register ezxml_ngremotemedia.xsl as custom XSL stylesheet for
     * XmlText field type.
     *
     * Avoids having it in %kernel.root_dir%/Resources folder
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ezpublish.siteaccess.list')) {
            return;
        }

        $scopes = array_merge(
            [ConfigResolver::SCOPE_DEFAULT],
            $container->getParameter('ezpublish.siteaccess.list'),
        );

        if (empty($scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if (!$container->hasParameter(sprintf('ezsettings.%s.fieldtypes.ezxml.custom_xsl', $scope))) {
                continue;
            }

            $xslConfig = $container->getParameter(sprintf('ezsettings.%s.fieldtypes.ezxml.custom_xsl', $scope));
            $xslConfig[] = ['path' => __DIR__ . '/../../Resources/xsl/ezxml_ngremotemedia.xsl', 'priority' => 5000];
            $container->setParameter(sprintf('ezsettings.%s.fieldtypes.ezxml.custom_xsl', $scope), $xslConfig);
        }
    }
}
