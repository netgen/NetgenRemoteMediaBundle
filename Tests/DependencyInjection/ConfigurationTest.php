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
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ),
            )
        );
    }

    public function testCompleteConfigurationIsOkAndValid()
    {
        $this->assertConfigurationIsValid(
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                    'system' => array(
                        'default' => array(
                            'image_variations' => array(
                                'default' => array(
                                    'full' => array(
                                        'transformations' => array(
                                            array(
                                                'name' => 'crop',
                                                'params' => array(2, 1),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'testsiteaccess' => array(
                            'image_variations' => array(
                                'test_content_type' => array(
                                    'medium' => array(
                                        'transformations' => array(
                                            array(
                                                'name' => 'crop',
                                                'params' => array(2, 1),
                                            ),
                                            array(
                                                'name' => 'test_transformation',
                                                'params' => array('test'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );
    }

    public function testEmptyAccountNameIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_name' => '',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ),
            )
        );
    }

    public function testEmptyAccountKeyIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => '',
                    'account_secret' => 'examplesecret',
                ),
            )
        );
    }

    public function testMissingAccountNameIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret',
                ),
            )
        );
    }

    public function testMissingAccountKeyIsInvalid()
    {
        $this->assertConfigurationIsInvalid(
            array(
                'netgen_remote_media' => array(
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_secret' => 'examplesecret',
                ),
            )
        );
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
