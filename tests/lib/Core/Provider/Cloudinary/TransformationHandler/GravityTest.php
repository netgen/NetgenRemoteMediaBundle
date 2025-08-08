<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Gravity;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Gravity::class)]
final class GravityTest extends TestCase
{
    protected Gravity $gravity;

    protected function setUp(): void
    {
        $this->gravity = new Gravity();
    }

    public function testGravityCompassPosition(): void
    {
        self::assertSame(
            ['gravity' => 'south'],
            $this->gravity->process(['south']),
        );
    }

    public function testGravitySpecialPosition(): void
    {
        self::assertSame(
            ['gravity' => 'face'],
            $this->gravity->process(['face']),
        );
    }

    public function testGravityObject(): void
    {
        self::assertSame(
            ['gravity' => 'microwave'],
            $this->gravity->process(['microwave']),
        );
    }

    public function testGravityWithDefaultAuto(): void
    {
        self::assertSame(
            [
                'gravity' => 'auto',
            ],
            $this->gravity->process(['auto']),
        );
    }

    public function testGravityWithAutoType(): void
    {
        self::assertSame(
            [
                'gravity' => 'auto:subject',
            ],
            $this->gravity->process(['auto', 'subject']),
        );
    }

    public function testWithoutConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->gravity->process();
    }
}
