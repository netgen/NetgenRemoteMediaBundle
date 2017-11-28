<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lfill;

class LfillTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lfill
     */
    protected $lfill;

    public function setUp()
    {
        parent::setUp();
        $this->lfill = new Lfill();
    }

    public function testLfillSimple()
    {
        $this->assertEquals(
            array('crop' => 'lfill'),
            $this->lfill->process($this->value, 'small')
        );
    }

    public function testLfillWithDimensions()
    {
        $this->assertEquals(
            array(
                'crop' => 'lfill',
                'width' => 100,
                'height' => 200
            ),
            $this->lfill->process($this->value, 'small', array(100, 200))
        );
    }
}
