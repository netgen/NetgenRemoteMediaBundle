<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Cloudinary;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\CloudinaryInstance as CloudinaryInstanceFactory;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CloudinaryInstanceFactory::class)]
final class CloudinaryInstanceTest extends AbstractTestCase
{
    protected CloudinaryInstanceFactory $cloudinaryInstanceFactory;

    protected function setUp(): void
    {
        $this->cloudinaryInstanceFactory = new CloudinaryInstanceFactory('myCloud', 'myKey', 'mySecret', 'myprefix');
    }

    public function testCreate(): void
    {
        $cloudinaryInstance = $this->cloudinaryInstanceFactory->create();

        self::assertInstanceOf(Cloudinary::class, $cloudinaryInstance);

        $newCloudinaryInstance = $this->cloudinaryInstanceFactory->create();

        self::assertSame(
            $cloudinaryInstance,
            $newCloudinaryInstance,
        );
    }
}
