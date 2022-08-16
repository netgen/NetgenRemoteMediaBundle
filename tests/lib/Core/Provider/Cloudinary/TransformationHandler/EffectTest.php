<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class EffectTest extends TestCase
{
    protected Effect $effect;

    protected function setUp(): void
    {
        $this->effect = new Effect();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testSimple(): void
    {
        self::assertSame(
            [
                'effect' => 'grayscale',
            ],
            $this->effect->process(['grayscale']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function test(): void
    {
        self::assertSame(
            [
                'effect' => 'saturation:50',
            ],
            $this->effect->process(['saturation', '50']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testMissingConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testInvalidConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process([]);
    }
}
