<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;

final class RemoteResourceLocationTest extends AbstractTest
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettings
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettingsForVariation
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getId
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getRemoteResource
     */
    public function test(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'test_remote_id',
            'type' => 'raw',
            'url' => 'https://cloudinary.com/test/upload/raw/test_remote_id',
            'name' => 'test_remote_id',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $location = new RemoteResourceLocation(
            $resource,
            [
                new CropSettings('small', 50, 80, 800, 400),
                new CropSettings('medium', 30, 50, 1200, 600),
                new CropSettings('large', 10, 25, 2000, 1000),
            ],
        );

        self::assertNull($location->getId());

        self::assertRemoteResourceSame(
            $resource,
            $location->getRemoteResource(),
        );

        self::assertCount(
            3,
            $location->getCropSettings(),
        );

        self::assertContainsOnlyInstancesOf(
            CropSettings::class,
            $location->getCropSettings(),
        );

        self::assertCropSettingsSame(
            new CropSettings('small', 50, 80, 800, 400),
            $location->getCropSettingsForVariation('small'),
        );

        self::assertCropSettingsSame(
            new CropSettings('medium', 30, 50, 1200, 600),
            $location->getCropSettingsForVariation('medium'),
        );

        self::assertCropSettingsSame(
            new CropSettings('large', 10, 25, 2000, 1000),
            $location->getCropSettingsForVariation('large'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::setCropSettings
     */
    public function testSetCropSettings(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'test_remote_id',
            'type' => 'raw',
            'url' => 'https://cloudinary.com/test/upload/raw/test_remote_id',
            'name' => 'test_remote_id',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $expected = new RemoteResourceLocation(
            $resource,
            [
                new CropSettings('small', 50, 80, 800, 400),
                new CropSettings('medium', 30, 50, 1200, 600),
                new CropSettings('large', 10, 25, 2000, 1000),
            ],
        );

        $location = new RemoteResourceLocation($resource);

        $location->setCropSettings([
            new CropSettings('small', 50, 80, 800, 400),
            new CropSettings('medium', 30, 50, 1200, 600),
            new CropSettings('large', 10, 25, 2000, 1000),
        ]);

        self::assertRemoteResourceLocationSame(
            $expected,
            $location,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettings
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettingsForVariation
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getRemoteResource
     */
    public function testWithNonExistingVariation(): void
    {
        $resource = new RemoteResource();

        $location = new RemoteResourceLocation(
            $resource,
            [
                new CropSettings('small', 50, 80, 800, 400),
            ],
        );

        self::expectException(CropSettingsNotFoundException::class);
        self::expectExceptionMessage('Crop settings for variation "large" were not found.');

        $location->getCropSettingsForVariation('large');
    }
}
