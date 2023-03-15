<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Utils;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use Netgen\RemoteMedia\Utils\CircularReferenceHandler;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CircularReferenceHandler::class)]
final class CircularReferenceHandlerTest extends AbstractTestCase
{
    protected CircularReferenceHandler $circularReferenceHandler;

    protected function setUp(): void
    {
        $this->circularReferenceHandler = new CircularReferenceHandler();
    }

    public function testWithRemoteResource(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'upload|image|folder/sample',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/folder/sample.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'sample',
        );

        self::assertSame(
            'upload|image|folder/sample',
            $this->circularReferenceHandler->__invoke($remoteResource),
        );
    }

    public function testWithRemoteResourceLocation(): void
    {
        $remoteResource = new RemoteResource(
            remoteId: 'upload|image|folder/sample',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/folder/sample.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'sample',
        );

        $remoteResourceLocation = new RemoteResourceLocation($remoteResource);

        self::assertSame(
            'upload|image|folder/sample',
            $this->circularReferenceHandler->__invoke($remoteResourceLocation),
        );
    }

    public function testWithOtherObject(): void
    {
        $cropSettings = new CropSettings(
            'variation1',
            0,
            0,
            200,
            100,
        );

        self::assertCropSettingsSame(
            $cropSettings,
            $this->circularReferenceHandler->__invoke($cropSettings),
        );
    }
}
