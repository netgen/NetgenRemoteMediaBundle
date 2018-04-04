<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Effect;

class EffectTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Effect
     */
    protected $effect;

    public function setUp()
    {
        parent::setUp();
        $this->effect = new Effect();
    }

    public function testEffectSimple()
    {
        $this->assertEquals(
            array(
                'effect' => 'grayscale'
            ),
            $this->effect->process($this->value, 'small', array('grayscale'))
        );
    }

    public function testEffect()
    {
        $this->assertEquals(
            array(
                'effect' => 'saturation:50'
            ),

            $this->effect->process($this->value, 'small', array('saturation', '50'))
        );
    }

    public function testCropVariationDoesNotExist()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->effect->process($this->value, 'large');
    }
}
