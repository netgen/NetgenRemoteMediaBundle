<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testBasicConfigurationValuesAreOkAndValid()
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ]
        );
    }

    public function testCompleteConfigurationIsOkAndValid()
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                    'system' => [
                        'default' => [
                            'image_variations' => [
                                'default' => [
                                    'full' => [
                                        'transformations' => [
                                            [
                                                'name' => 'crop',
                                                'params' => [2, 1],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'testsiteaccess' => [
                            'image_variations' => [
                                'test_content_type' => [
                                    'medium' => [
                                        'transformations' => [
                                            [
                                                'name' => 'crop',
                                                'params' => [2, 1],
                                            ],
                                            [
                                                'name' => 'test_transformation',
                                                'params' => ['test'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testEmptyAccountNameIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => '',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ]
        );
    }

    public function testEmptyAccountKeyIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => '',
                    'account_secret' => 'examplesecret',
                ],
            ]
        );
    }

    public function testMissingAccountNameIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ]
        );
    }

    public function testMissingAccountKeyIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_secret' => 'examplesecret',
                ],
            ]
        );
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
