<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\NetgenRemoteMediaExtension;

class NetgenRemoteMediaExtensionTest extends AbstractExtensionTestCase
{
    public function testItSetsValidContainerParameters()
    {
        $this->load();
    }

    protected function getContainerExtensions()
    {
        return array(
            new NetgenRemoteMediaExtension(),
        );
    }

    protected function getMinimalConfiguration()
    {
        return array(
            'provider' => 'testprovider',
            'account_name' => 'testname',
            'account_key' => 'testkey',
            'account_secret' => 'testsecret',
            'system' => array(
                'default' => array(
                    'image_variations' => array(
                        'ng_frontpage' => array(
                            'small' => array(
                                'transformations' => array(
                                    'name' => array('Crop'),
                                    'params' => array(200, 200),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
