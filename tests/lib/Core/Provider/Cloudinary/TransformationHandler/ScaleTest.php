<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale;

final class ScaleTest extends BaseTest
{
    protected Scale $scale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scale = new Scale();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale::process
     */
    public function testScaleSimple(): void
    {
        self::assertSame(
            ['crop' => 'scale'],
            $this->scale->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale::process
     */
    public function testScaleWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'scale',
                'width' => 100,
                'height' => 200,
            ],
            $this->scale->process($this->resource, 'small', [100, 200]),
        );
    }
}
