<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit;

final class FitTest extends BaseTest
{
    protected Fit $fit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fit = new Fit();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit::process
     */
    public function testFitSimple(): void
    {
        self::assertSame(
            ['crop' => 'fit'],
            $this->fit->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit::process
     */
    public function testFitWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'fit',
                'width' => 100,
                'height' => 200,
            ],
            $this->fit->process($this->resource, 'small', [100, 200]),
        );
    }
}
