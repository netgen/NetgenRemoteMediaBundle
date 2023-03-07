<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class ResizeTest extends TestCase
{
    protected Resize $resize;

    protected function setUp(): void
    {
        $this->resize = new Resize();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize::process
     */
    public function testWithWidth(): void
    {
        self::assertSame(
            [
                'width' => 100,
            ],
            $this->resize->process([100]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize::process
     */
    public function testWithBothDimensions(): void
    {
        self::assertSame(
            [
                'width' => 100,
                'height' => 50,
            ],
            $this->resize->process([100, 50]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize::process
     */
    public function testWithoutConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->resize->process();
    }
}
