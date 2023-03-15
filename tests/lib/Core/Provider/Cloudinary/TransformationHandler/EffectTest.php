<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Effect::class)]
final class EffectTest extends TestCase
{
    protected Effect $effect;

    protected function setUp(): void
    {
        $this->effect = new Effect();
    }

    public function testSimple(): void
    {
        self::assertSame(
            [
                'effect' => 'grayscale',
            ],
            $this->effect->process(['grayscale']),
        );
    }

    public function test(): void
    {
        self::assertSame(
            [
                'effect' => 'saturation:50',
            ],
            $this->effect->process(['saturation', '50']),
        );
    }

    public function testMissingConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process();
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process([]);
    }
}
