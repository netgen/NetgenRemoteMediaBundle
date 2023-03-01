<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testBasicConfigurationValuesAreOkAndValid(): void
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testCompleteConfigurationIsOkAndValid(): void
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                    'cache' => [
                        'pool' => 'cache.app',
                        'ttl' => 7200,
                    ],
                    'cloudinary' => [
                        'cache_requests' => true,
                        'log_requests' => false,
                        'encryption_key' => null,
                    ],
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
                        'test_group' => [
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
                    'named_objects' => [
                        'remote_resource' => [
                            'test_resource' => 'upload|image|folder/test_resource',
                            'test_resource2' => 'upload|image|folder/test_resource2',
                        ],
                        'remote_resource_location' => [
                            'test_location' => [
                                'resource_remote_id' => 'upload|image|folder/test_resource',
                                'source' => 'test_resource',
                                'watermark_text' => 'Netgen',
                            ],
                            'test_location2' => [
                                'resource_remote_id' => 'upload|image|folder/test_resource2',
                                'source' => 'test_resource2',
                                'watermark_text' => 'Remote Media',
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testEmptyAccountNameIsInvalid(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => '',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testEmptyAccountKeyIsInvalid(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => '',
                    'account_secret' => 'examplesecret',
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testMissingAccountNameIsInvalid(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testMissingAccountKeyIsInvalid(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_secret' => 'examplesecret',
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addNamedObjectsConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addProviderSection
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     *
     * @dataProvider invalidNamedObjectsProvider
     */
    public function testInvalidNamedObjectsConfiguration(array $configuration): void
    {
        $this->assertConfigurationIsInvalid($configuration);
    }

    public function invalidNamedObjectsProvider(): array
    {
        return [
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'resource' => [
                                'test' => 'test_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'location' => [
                                'test' => 'test_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'remote_resource' => [
                                'test_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'remote_resource_location' => [
                                'test' => 'test_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'remote_resource_location' => [
                                'test' => [
                                    'id' => 'test',
                                    'source' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'netgen_remote_media' => [
                        'provider' => 'cloudinary',
                        'account_name' => 'examplename',
                        'account_key' => 'examplekey',
                        'account_secret' => 'examplesecret',
                        'named_objects' => [
                            'remote_resource_location' => [
                                'test' => [
                                    'resource_remote_id' => 'test',
                                    'crop' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
