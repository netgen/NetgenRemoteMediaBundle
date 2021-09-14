<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Limit;

final class LimitTest extends BaseTest
{
    protected Limit $limit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limit = new Limit();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Limit::process
     */
    public function testLimitSimple(): void
    {
        self::assertSame(
            ['crop' => 'limit'],
            $this->limit->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Limit::process
     */
    public function testLimitWithDimensions(): void
    {
        self::assertSame(
            [
                'crop' => 'limit',
                'width' => 100,
                'height' => 200,
            ],
            $this->limit->process($this->resource, 'small', [100, 200]),
        );
    }
}
