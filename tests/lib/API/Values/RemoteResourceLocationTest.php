<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;
use PHPUnit\Framework\TestCase;

final class RemoteResourceLocationTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettings
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getCropSettingsForVariation
     * @covers \Netgen\RemoteMedia\API\Values\RemoteResourceLocation::getRemoteResource
     */
    public function test(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'test_remote_id',
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

        self::assertInstanceOf(
            RemoteResource::class,
            $location->getRemoteResource(),
        );

        self::assertSame(
            'test_remote_id',
            $location->getRemoteResource()->getRemoteId(),
        );

        self::assertCount(
            3,
            $location->getCropSettings(),
        );

        self::assertContainsOnlyInstancesOf(
            CropSettings::class,
            $location->getCropSettings(),
        );

        self::assertInstanceOf(
            CropSettings::class,
            $location->getCropSettingsForVariation('medium'),
        );

        self::assertSame(
            'small',
            $location->getCropSettingsForVariation('small')->getVariationName(),
        );

        self::assertSame(
            1200,
            $location->getCropSettingsForVariation('medium')->getWidth(),
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
