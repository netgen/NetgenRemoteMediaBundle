<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension;

class NetgenRemoteMediaExtensionTest extends AbstractExtensionTestCase
{
    public function testItSetsValidContainerParameters()
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
     * @expectedException \InvalidArgumentException
     */
    public function testWithoutProviderParameter()
    {
        $this->load(['provider' => null]);
    }

    protected function getContainerExtensions()
    {
        return [
            new NetgenRemoteMediaExtension(),
        ];
    }

    protected function getMinimalConfiguration()
    {
        return [
            'provider' => 'testprovider',
            'account_name' => 'testname',
            'account_key' => 'testkey',
            'account_secret' => 'testsecret',
            'system' => [
                'default' => [
                    'image_variations' => [
                        'ng_frontpage' => [
                            'small' => [
                                'transformations' => [
                                    'name' => ['Crop'],
                                    'params' => [200, 200],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
