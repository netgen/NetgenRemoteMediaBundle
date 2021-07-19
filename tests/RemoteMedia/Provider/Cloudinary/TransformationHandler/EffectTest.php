<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Effect;

class EffectTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Effect
     */
    protected $effect;

    protected function setUp()
    {
        parent::setUp();
        $this->effect = new Effect();
    }

    public function testEffectSimple()
    {
        self::assertEquals(
            [
                'effect' => 'grayscale',
            ],
            $this->effect->process($this->value, 'small', ['grayscale']),
        );
    }

    public function testEffect()
    {
        self::assertEquals(
            [
                'effect' => 'saturation:50',
            ],
            $this->effect->process($this->value, 'small', ['saturation', '50']),
        );
    }

    public function testCropVariationDoesNotExist()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process($this->value, 'large');
    }
}
