<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad;

final class LpadTest extends BaseTest
{
    protected Lpad $lpad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lpad = new Lpad();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad::process
     */
    public function testLpadSimple(): void
    {
        self::assertSame(
            ['crop' => 'lpad'],
            $this->lpad->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad::process
     */
    public function testLpadWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200,
            ],
            $this->lpad->process($this->resource, 'small', [100, 200]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad::process
     */
    public function testLpadWithDimensionsAndColour(): void
    {
        self::assertSame(
            [
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200,
                'background' => 'red',
            ],
            $this->lpad->process($this->resource, 'small', [100, 200, 'red']),
        );
    }
}
