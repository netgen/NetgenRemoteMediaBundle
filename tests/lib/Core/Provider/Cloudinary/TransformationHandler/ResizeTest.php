<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class ResizeTest extends BaseTest
{
    protected Resize $resize;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resize = new Resize();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize::process
     */
    public function testResizeWithDimensions(): void
    {
        self::assertSame(
            [
                'width' => 100,
                'height' => 200,
            ],
            $this->resize->process($this->resource, 'small', [100, 200]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize::process
     */
    public function testMissingResizeConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->resize->process($this->resource, 'named');
    }
}
