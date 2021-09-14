<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill;

final class FillTest extends BaseTest
{
    protected Fill $fill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fill = new Fill();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill::process
     */
    public function testFillSimple(): void
    {
        self::assertSame(
            ['crop' => 'fill'],
            $this->fill->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill::process
     */
    public function testFillWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'fill',
                'width' => 100,
                'height' => 200,
            ],
            $this->fill->process($this->resource, 'small', [100, 200]),
        );
    }
}
