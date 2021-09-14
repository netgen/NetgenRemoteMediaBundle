<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class EffectTest extends BaseTest
{
    protected Effect $effect;

    protected function setUp(): void
    {
        parent::setUp();
        $this->effect = new Effect();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testEffectSimple(): void
    {
        self::assertSame(
            [
                'effect' => 'grayscale',
            ],
            $this->effect->process($this->resource, 'small', ['grayscale']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testEffect(): void
    {
        self::assertSame(
            [
                'effect' => 'saturation:50',
            ],
            $this->effect->process($this->resource, 'small', ['saturation', '50']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Effect::process
     */
    public function testCropVariationDoesNotExist(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process($this->resource, 'large');
    }
}
