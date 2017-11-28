<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Mfit;

class MfitTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Mfit
     */
    protected $mfit;

    public function setUp()
    {
        parent::setUp();
        $this->mfit = new Mfit();
    }

    public function testMfitSimple()
    {
        $this->assertEquals(
            array('crop' => 'mfit'),
            $this->mfit->process($this->value, 'small')
        );
    }

    public function testMfitWithDimensions()
    {
        $this->assertEquals(
            array(
                'crop' => 'mfit',
                'width' => 100,
                'height' => 200
            ),
            $this->mfit->process($this->value, 'small', array(100, 200))
        );
    }
}
