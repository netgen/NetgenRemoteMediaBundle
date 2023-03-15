<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoteResourceLocation::class)]
final class RemoteResourceLocationTest extends AbstractTestCase
{
    public function test(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_remote_id',
            type: 'raw',
            url: 'https://cloudinary.com/test/upload/raw/test_remote_id',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_remote_id',
        );

        $location = new RemoteResourceLocation(
            $resource,
            'my_source',
            [
                new CropSettings('small', 50, 80, 800, 400),
                new CropSettings('medium', 30, 50, 1200, 600),
                new CropSettings('large', 10, 25, 2000, 1000),
            ],
            'Test text',
        );

        self::assertNull($location->getId());

        self::assertRemoteResourceSame(
            $resource,
            $location->getRemoteResource(),
        );

        self::assertSame(
            'my_source',
            $location->getSource(),
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

        self::assertSame(
            'Test text',
            $location->getWatermarkText(),
        );
    }

    public function testSetCropSettings(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_remote_id',
            type: 'raw',
            url: 'https://cloudinary.com/test/upload/raw/test_remote_id',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_remote_id',
        );

        $expected = new RemoteResourceLocation(
            $resource,
            null,
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

    public function testSetWatermarkText(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_remote_id',
            type: 'raw',
            url: 'https://cloudinary.com/test/upload/raw/test_remote_id',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_remote_id',
        );

        $expected = new RemoteResourceLocation(
            $resource,
            null,
            [],
            'Some watermark',
        );

        $location = new RemoteResourceLocation($resource);

        self::assertNull($location->getWatermarkText());

        $location->setWatermarkText('Some watermark');

        self::assertRemoteResourceLocationSame(
            $expected,
            $location,
        );
    }

    public function testSetSource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_remote_id',
            type: 'raw',
            url: 'https://cloudinary.com/test/upload/raw/test_remote_id',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_remote_id',
        );

        $expected = new RemoteResourceLocation(
            $resource,
            'my_source',
        );

        $location = new RemoteResourceLocation($resource);

        self::assertNull($location->getSource());

        $location->setSource('my_source');

        self::assertRemoteResourceLocationSame(
            $expected,
            $location,
        );
    }

    public function testWithNonExistingVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_remote_id',
            type: 'raw',
            url: 'https://cloudinary.com/test/upload/raw/test_remote_id',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: 'test_remote_id',
        );

        $location = new RemoteResourceLocation(
            $resource,
            null,
            [
                new CropSettings('small', 50, 80, 800, 400),
            ],
        );

        self::expectException(CropSettingsNotFoundException::class);
        self::expectExceptionMessage('Crop settings for variation "large" were not found.');

        $location->getCropSettingsForVariation('large');
    }
}
