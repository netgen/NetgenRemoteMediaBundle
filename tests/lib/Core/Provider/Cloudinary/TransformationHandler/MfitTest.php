<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mfit;

final class MfitTest extends BaseTest
{
    protected Mfit $mfit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mfit = new Mfit();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mfit::process
     */
    public function testMfitSimple(): void
    {
        self::assertSame(
            ['crop' => 'mfit'],
            $this->mfit->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mfit::process
     */
    public function testMfitWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'mfit',
                'width' => 100,
                'height' => 200,
            ],
            $this->mfit->process($this->resource, 'small', [100, 200]),
        );
    }
}
