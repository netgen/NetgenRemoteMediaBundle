<?php

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class NetgenRemoteMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['provider'])) {
            throw new \InvalidArgumentException('The "provider" option must be set');
        }

        $loader = $this->loadSettings($container);

        $container->setParameter("netgen_remote_media.parameters.{$config['provider']}.account_name",
            $config['account_name']);
        $container->setParameter("netgen_remote_media.parameters.{$config['provider']}.account_key",
            $config['account_key']);
        $container->setParameter("netgen_remote_media.parameters.{$config['provider']}.account_secret",
            $config['account_secret']);

        $container->setParameter('netgen_remote_media.remove_unused_resources', $config['remove_unused']);
        $container->setAlias('netgen_remote_media.provider', 'netgen_remote_media.provider.' . $config['provider']);

        $processor = new ConfigurationProcessor($container, 'netgen_remote_media');
        $processor->mapConfigArray('image_variations', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        $this->loadContentBrowserSettings($activatedBundles, $container);
        $this->loadOpenGraphSettings($activatedBundles, $loader);
        $this->loadPersistenceCacheServices($activatedBundles, $loader);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/default_settings.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('netgen_remote_media', $config);
        $container->addResource(new FileResource($configFile));

        $configFile = __DIR__ . '/../Resources/config/ezpublish.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        $configs = [];
        if (in_array('EzPlatformAdminUiBundle', $activatedBundles, true)) {
            $configs['ezadminui/twig.yml'] = 'twig';
        }

        foreach ($configs as $fileName => $extensionName) {
            $configFile = __DIR__ . '/../Resources/config/' . $fileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $fileName
     * @param string $configName
     */
    protected function doPrepend(ContainerBuilder $container, $fileName, $configName)
    {
        $configFile = __DIR__ . '/../Resources/config/' . $fileName;
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig($configName, $config);
        $container->addResource(new FileResource($configFile));
    }

    private function loadPersistenceCacheServices(array $activatedBundles, Loader\YamlFileLoader $loader)
    {
        // We're using the existence of EzPlatformAdminUiBundle (Admin UI v2)
        // as the means of detecting eZ kernel version 6 or 7
        $persistenceCache = in_array('EzPlatformAdminUiBundle', $activatedBundles, true) ? 'psr6' : 'stash';

        $loader->load('storage/cache_' . $persistenceCache . '.yml');
    }

    private function loadContentBrowserSettings(array $activatedBundles, ContainerBuilder $container)
    {
        if (in_array('NetgenContentBrowserBundle', $activatedBundles, true)) {
            $container->setParameter('netgen_remote_media.content_browser.activated', true);
            $this->doPrepend($container, 'content_browser/cloudinary.yml', 'netgen_content_browser');
        } else {
            $container->setParameter('netgen_remote_media.content_browser.activated', false);
        }
    }

    private function loadOpenGraphSettings(array $activatedBundles, Loader\YamlFileLoader $loader)
    {
        if (in_array('NetgenOpenGraphBundle', $activatedBundles, true)) {
            $loader->load('opengraph.yml');
        }
    }

    private function loadSettings(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('default_parameters.yml');
        $loader->load('transformation_handlers.yml');
        $loader->load('services.yml');
        $loader->load('templating.yml');
        $loader->load('fieldtypes.yml');

        return $loader;
    }
}
