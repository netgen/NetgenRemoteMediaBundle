<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Opacity;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Opacity::class)]
final class OpacityTest extends TestCase
{
    protected Opacity $opacity;

    protected function setUp(): void
    {
        $this->opacity = new Opacity();
    }

    public function test(): void
    {
        self::assertSame(
            [
                'opacity' => 20,
            ],
            $this->opacity->process([20]),
        );
    }

    public function testMissingConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->opacity->process();
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->opacity->process([]);
    }
}
