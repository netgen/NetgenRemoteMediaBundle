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
        $this->assertContainerBuilderHasParameter('netgen_remote_media.parameters.testprovider.upload_prefix', 'testprefix');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.remove_unused_resources', false);
        $this->assertContainerBuilderHasParameter('netgen_remote_media.cache.pool_name', 'cache.app');
        $this->assertContainerBuilderHasParameter('netgen_remote_media.cache.ttl', 3600);
        $this->assertContainerBuilderHasParameter('netgen_remote_media.encryption_key', 'dsf45z45hh45f43f43f');

        $this->assertContainerBuilderHasParameter(
            'netgen_remote_media.named_remote_resources',
            [
                'my_resource' => 'my_resource_id',
            ],
        );

        $this->assertContainerBuilderHasParameter(
            'netgen_remote_media.named_remote_resource_locations',
            [
                'my_resource_location' => [
                    'resource_remote_id' => 'my_resource_id',
                    'source' => 'named_my_resource_location',
                    'watermark_text' => 'Netgen.io',
                ],
            ],
        );

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
            'upload_prefix' => 'testprefix',
            'cache' => [
                'pool' => 'cache.app',
                'ttl' => 3600,
            ],
            'cloudinary' => [
                'cache_requests' => true,
                'log_requests' => false,
                'encryption_key' => 'dsf45z45hh45f43f43f',
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
            'named_objects' => [
                'remote_resource' => [
                    'my_resource' => 'my_resource_id',
                ],
                'remote_resource_location' => [
                    'my_resource_location' => [
                        'resource_remote_id' => 'my_resource_id',
                        'source' => 'named_my_resource_location',
                        'watermark_text' => 'Netgen.io',
                    ],
                ],
            ],
        ];
    }
}
