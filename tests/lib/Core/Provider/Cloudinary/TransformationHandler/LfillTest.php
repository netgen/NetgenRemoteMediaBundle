<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill;

final class LfillTest extends BaseTest
{
    protected Lfill $lfill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lfill = new Lfill();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill::process
     */
    public function testLfillSimple(): void
    {
        self::assertSame(
            ['crop' => 'lfill'],
            $this->lfill->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill::process
     */
    public function testLfillWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'lfill',
                'width' => 100,
                'height' => 200,
            ],
            $this->lfill->process($this->resource, 'small', [100, 200]),
        );
    }
}
