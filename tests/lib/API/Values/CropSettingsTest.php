<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\CropSettings;
use PHPUnit\Framework\TestCase;

final class CropSettingsTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::__construct
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getHeight
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getVariationName
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getWidth
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getX
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getY
     */
    public function testCreate(): void
    {
        $cropSettings = new CropSettings(
            'small',
            10,
            5,
            1920,
            1080,
        );

        self::assertSame(
            'small',
            $cropSettings->getVariationName(),
        );

        self::assertSame(
            10,
            $cropSettings->getX(),
        );

        self::assertSame(
            5,
            $cropSettings->getY(),
        );

        self::assertSame(
            1920,
            $cropSettings->getWidth(),
        );

        self::assertSame(
            1080,
            $cropSettings->getHeight(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::__construct
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::fromArray
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getHeight
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getVariationName
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getWidth
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getX
     * @covers \Netgen\RemoteMedia\API\Values\CropSettings::getY
     */
    public function testCreateFromArray(): void
    {
        $cropSettings = CropSettings::fromArray(
            'small',
            [
                'x' => 10,
                'y' => 5,
                'width' => 1920,
                'height' => 1080,
            ],
        );

        self::assertSame(
            'small',
            $cropSettings->getVariationName(),
        );

        self::assertSame(
            10,
            $cropSettings->getX(),
        );

        self::assertSame(
            5,
            $cropSettings->getY(),
        );

        self::assertSame(
            1920,
            $cropSettings->getWidth(),
        );

        self::assertSame(
            1080,
            $cropSettings->getHeight(),
        );
    }
}
