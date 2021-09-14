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
