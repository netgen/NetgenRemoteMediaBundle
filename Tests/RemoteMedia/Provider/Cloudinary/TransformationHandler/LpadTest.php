<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lpad;

class LpadTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lpad
     */
    protected $lpad;

    public function setUp()
    {
        parent::setUp();
        $this->lpad = new Lpad();
    }

    public function testLpadSimple()
    {
        $this->assertEquals(
            array('crop' => 'lpad'),
            $this->lpad->process($this->value, 'small')
        );
    }

    public function testLpadWithDimensions()
    {
        $this->assertEquals(
            array(
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200
            ),
            $this->lpad->process($this->value, 'small', array(100, 200))
        );
    }

    public function testLpadWithDimensionsAndColour()
    {
        $this->assertEquals(
            array(
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200,
                'background' => 'red'
            ),
            $this->lpad->process($this->value, 'small', array(100, 200, 'red'))
        );
    }
}
