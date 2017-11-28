<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Scale;

class ScaleTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Scale
     */
    protected $scale;

    public function setUp()
    {
        parent::setUp();
        $this->scale = new Scale();
    }

    public function testScaleSimple()
    {
        $this->assertEquals(
            array('crop' => 'scale'),
            $this->scale->process($this->value, 'small')
        );
    }

    public function testScaleWithDimensions()
    {
        $this->assertEquals(
            array(
                'crop' => 'scale',
                'width' => 100,
                'height' => 200
            ),
            $this->scale->process($this->value, 'small', array(100, 200))
        );
    }
}
