<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension;

class NetgenRemoteMediaExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new NetgenRemoteMediaExtension(),
        ];
    }

    public function testItSetsValidContainerParameters()
    {
        $this->load();
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
                                    'params' => [200,200]
                                ]
                            ]
                        ],
                    ]
                ],
            ],
        ];
    }
}
