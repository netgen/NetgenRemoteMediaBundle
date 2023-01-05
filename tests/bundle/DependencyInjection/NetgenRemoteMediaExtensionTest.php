<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use InvalidArgumentException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension;

final class NetgenRemoteMediaExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension::load
     */
    public function testItSetsValidContainerParameters(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasParameter('netgen_remote_media.parameters.testprovider.account_name', 'testname');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.parameters.testprovider.account_key', 'testkey');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.parameters.testprovider.account_secret', 'testsecret');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.remove_unused_resources', false);
        $this->assertContainerBuilderHasParameter('netgen_remote_media.cache.pool_name', 'cache.app');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.cache.ttl', 3600);

        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway.inner', 'netgen_remote_media.provider.cloudinary.gateway.api');
        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway', 'netgen_remote_media.provider.cloudinary.gateway.cached');
        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider', 'netgen_remote_media.provider.testprovider');
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension::load
     */
    public function testWithoutProviderParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->load(['provider' => null]);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension::load
     */
    public function testWithEnabledCloudinaryLogging(): void
    {
        $this->load([
            'cloudinary' => [
                'log_requests' => true,
            ],
        ]);

        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway.inner', 'netgen_remote_media.provider.cloudinary.gateway.logged');
        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway', 'netgen_remote_media.provider.cloudinary.gateway.cached');
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension::load
     */
    public function testWithDisabledCloudinaryCaching(): void
    {
        $this->load([
            'cloudinary' => [
                'cache_requests' => false,
            ],
        ]);

        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway.inner', 'netgen_remote_media.provider.cloudinary.gateway.api');
        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway', 'netgen_remote_media.provider.cloudinary.gateway.inner');
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension::load
     */
    public function testWithDisabledCloudinaryCachingAndEnabledLogging(): void
    {
        $this->load([
            'cloudinary' => [
                'cache_requests' => false,
                'log_requests' => true,
            ],
        ]);

        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway.inner', 'netgen_remote_media.provider.cloudinary.gateway.logged');
        $this->assertContainerBuilderHasAlias('netgen_remote_media.provider.cloudinary.gateway', 'netgen_remote_media.provider.cloudinary.gateway.inner');
    }

    protected function getContainerExtensions(): array
    {
        return [
            new NetgenRemoteMediaExtension(),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'provider' => 'testprovider',
            'account_name' => 'testname',
            'account_key' => 'testkey',
            'account_secret' => 'testsecret',
            'cache' => [
                'pool' => 'cache.app',
                'ttl' => 3600,
            ],
            'cloudinary' => [
                'cache_requests' => true,
                'log_requests' => false,
            ],
            'image_variations' => [
                'test_group' => [
                    'small' => [
                        'transformations' => [
                            'name' => ['Crop'],
                            'params' => [200, 200],
                        ],
                    ],
                ],
            ],
        ];
    }
}
