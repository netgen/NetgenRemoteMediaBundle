<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Resize::class)]
final class ResizeTest extends TestCase
{
    protected Resize $resize;

    protected function setUp(): void
    {
        $this->resize = new Resize();
    }

    public function testWithWidth(): void
    {
        self::assertSame(
            [
                'width' => 100,
            ],
            $this->resize->process([100]),
        );
    }

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

    public function testWithoutConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->resize->process();
    }
}
