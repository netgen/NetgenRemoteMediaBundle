<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

use function file_get_contents;
use function sprintf;

final class NetgenRemoteMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DelegatingLoader(
            new LoaderResolver(
                [
                    new GlobFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                ],
            ),
        );

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('netgen_remote_media.remove_unused_resources', $config['remove_unused']);
        $container->setAlias('netgen_remote_media.provider', 'netgen_remote_media.provider.' . $config['provider']);

        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_name', $config['provider']),
            $config['account_name'],
        );
        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_key', $config['provider']),
            $config['account_key'],
        );
        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.account_secret', $config['provider']),
            $config['account_secret'],
        );
        $container->setParameter(
            sprintf('netgen_remote_media.parameters.%s.upload_prefix', $config['provider']),
            $config['upload_prefix'],
        );
        $container->setParameter(
            'netgen_remote_media.image_variations',
            $config['image_variations'],
        );
        $container->setParameter(
            'netgen_remote_media.named_remote_resources',
            $config['named_objects']['remote_resource'] ?? [],
        );
        $container->setParameter(
            'netgen_remote_media.named_remote_resource_locations',
            $config['named_objects']['remote_resource_location'] ?? [],
        );
        $container->setParameter(
            'netgen_remote_media.cache.pool_name',
            $config['cache']['pool'],
        );
        $container->setParameter(
            'netgen_remote_media.cache.ttl',
            $config['cache']['ttl'],
        );
        $container->setParameter(
            'netgen_remote_media.encryption_key',
            $config['cloudinary']['encryption_key'],
        );

        $cloudinaryInnerGatewayAlias = $config['cloudinary']['log_requests']
            ? 'netgen_remote_media.provider.cloudinary.gateway.logged'
            : 'netgen_remote_media.provider.cloudinary.gateway.api';

        $container->setAlias('netgen_remote_media.provider.cloudinary.gateway.inner', $cloudinaryInnerGatewayAlias);

        $cloudinaryGatewayAlias = $config['cloudinary']['cache_requests']
            ? 'netgen_remote_media.provider.cloudinary.gateway.cached'
            : 'netgen_remote_media.provider.cloudinary.gateway.inner';

        $container->setAlias('netgen_remote_media.provider.cloudinary.gateway', $cloudinaryGatewayAlias);

        $container->setParameter(
            'netgen_remote_media.cloudinary.folder_mode',
            $config['cloudinary']['folder_mode'],
        );

        $loader->load('default_parameters.yaml');
        $loader->load('services/**/*.yaml', 'glob');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $prependConfigs = [
            'default_settings.yaml' => 'netgen_remote_media',
            'doctrine.yaml' => 'doctrine',
            'framework.yaml' => 'framework',
            'monolog.yaml' => 'monolog',
            'twig.yaml' => 'twig',
        ];

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse((string) file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
