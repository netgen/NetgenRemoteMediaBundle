<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Utils;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Tests\AbstractTest;
use Netgen\RemoteMedia\Utils\CircularReferenceHandler;

final class CircularReferenceHandlerTest extends AbstractTest
{
    protected CircularReferenceHandler $circularReferenceHandler;

    protected function setUp(): void
    {
        $this->circularReferenceHandler = new CircularReferenceHandler();
    }

    /**
     * @covers \Netgen\RemoteMedia\Utils\CircularReferenceHandler::__invoke
     */
    public function testWithRemoteResource(): void
    {
        $remoteResource = new RemoteResource([
            'remoteId' => 'upload|image|folder/sample',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/sample.jpg',
            'name' => 'sample',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        self::assertSame(
            'upload|image|folder/sample',
            $this->circularReferenceHandler->__invoke($remoteResource),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Utils\CircularReferenceHandler::__invoke
     */
    public function testWithRemoteResourceLocation(): void
    {
        $remoteResource = new RemoteResource([
            'remoteId' => 'upload|image|folder/sample',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/sample.jpg',
            'name' => 'sample',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $remoteResourceLocation = new RemoteResourceLocation($remoteResource);

        self::assertSame(
            'upload|image|folder/sample',
            $this->circularReferenceHandler->__invoke($remoteResourceLocation),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Utils\CircularReferenceHandler::__invoke
     */
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
