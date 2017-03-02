<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Netgen\Bundle\RemoteMediaBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testBasicConfigurationValuesAreOkAndValid()
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_remote_media' => [
                    'provider' => 'cloudinary',
                    'account_name' => 'examplename',
                    'account_key' => 'examplekey',
                    'account_secret' => 'examplesecret'
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
                    'account_secret' => 'examplesecret'
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
                    'account_secret' => 'examplesecret'
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
                    'account_secret' => 'examplesecret'
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
                    'account_secret' => 'examplesecret'
                ],
            ]
        );
    }
}
