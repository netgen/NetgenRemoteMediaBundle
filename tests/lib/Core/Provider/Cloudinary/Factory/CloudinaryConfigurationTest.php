<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\CloudinaryConfiguration as CloudinaryConfigurationFactory;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\CloudinaryConfigurationInitializer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CloudinaryConfigurationFactory::class)]
final class CloudinaryConfigurationTest extends AbstractTestCase
{
    protected CloudinaryConfigurationFactory $cloudinaryConfigurationFactory;

    protected function setUp(): void
    {
        $this->cloudinaryConfigurationFactory = new CloudinaryConfigurationFactory(
            CloudinaryConfigurationInitializer::CLOUD_NAME,
            CloudinaryConfigurationInitializer::API_KEY,
            CloudinaryConfigurationInitializer::API_SECRET,
            CloudinaryConfigurationInitializer::UPLOAD_PREFIX,
            false,
        );
    }

    public function testCreate(): void
    {
        self::assertSame(
            CloudinaryConfigurationInitializer::getConfiguration(),
            $this->cloudinaryConfigurationFactory->create(),
        );
    }
}
