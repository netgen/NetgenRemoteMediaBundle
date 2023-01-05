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
                ],
            ],
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addCloudinaryConfiguration
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration::addImageConfiguration
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

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
