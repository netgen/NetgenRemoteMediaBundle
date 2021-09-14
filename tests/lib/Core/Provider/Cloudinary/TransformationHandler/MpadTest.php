<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad;

final class MpadTest extends BaseTest
{
    protected Mpad $mpad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mpad = new Mpad();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad::process
     */
    public function testMpadSimple(): void
    {
        self::assertSame(
            ['crop' => 'mpad'],
            $this->mpad->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad::process
     */
    public function testMpadWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'mpad',
                'width' => 100,
                'height' => 200,
            ],
            $this->mpad->process($this->resource, 'small', [100, 200]),
        );
    }
}
