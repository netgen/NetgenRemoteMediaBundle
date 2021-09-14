<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Pad;

final class PadTest extends BaseTest
{
    protected Pad $pad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pad = new Pad();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Pad::process
     */
    public function testPadSimple(): void
    {
        self::assertSame(
            ['crop' => 'pad'],
            $this->pad->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Pad::process
     */
    public function testPadWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'pad',
                'width' => 100,
                'height' => 200,
            ],
            $this->pad->process($this->resource, 'small', [100, 200]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Pad::process
     */
    public function testPadWithDimensionsAndColour(): void
    {
        self::assertSame(
            [
                'crop' => 'pad',
                'width' => 100,
                'height' => 200,
                'background' => 'red',
            ],
            $this->pad->process($this->resource, 'small', [100, 200, 'red']),
        );
    }
}
