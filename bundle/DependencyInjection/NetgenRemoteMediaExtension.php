<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;
use function array_keys;
use function file_get_contents;
use function in_array;
use function sprintf;

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
            throw new InvalidArgumentException('The "provider" option must be set');
        }

        $loader = $this->loadSettings($container);

        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_name', $config['provider']),
            $config['account_name']
        );
        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_key', $config['provider']),
            $config['account_key']
        );
        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_secret', $config['provider']),
            $config['account_secret']
        );

        $container->setParameter('netgen_remote_media.remove_unused_resources', $config['remove_unused']);
        $container->setAlias('netgen_remote_media.provider', 'netgen_remote_media.provider.' . $config['provider']);

        $processor = new ConfigurationProcessor($container, 'netgen_remote_media');
        $processor->mapConfigArray('image_variations', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        $this->loadOpenGraphSettings($activatedBundles, $loader);
        $this->loadLegacySettings($activatedBundles, $loader);
    }

    /**
     * Allow an extension to prepend the extension configurations.
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

        if (in_array('EzPlatformRichTextBundle', $activatedBundles, true)) {
            $configs['ezadminui/ezrichtext.yml'] = 'ezrichtext';

            $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
            $loader->load('ezadminui/custom_tags.yml');
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

    private function loadOpenGraphSettings(array $activatedBundles, YamlFileLoader $loader)
    {
        if (in_array('NetgenOpenGraphBundle', $activatedBundles, true)) {
            $loader->load('opengraph.yml');
        }
    }

    private function loadLegacySettings(array $activatedBundles, YamlFileLoader $loader)
    {
        if (in_array('EzPublishLegacyBundle', $activatedBundles, true)) {
            $loader->load('legacy.yml');
        }
    }

    private function loadSettings(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('default_parameters.yml');
        $loader->load('transformation_handlers.yml');
        $loader->load('services.yml');
        $loader->load('templating.yml');
        $loader->load('fieldtypes.yml');
        $loader->load('storage/cache_psr6.yml');

        return $loader;
    }
}
