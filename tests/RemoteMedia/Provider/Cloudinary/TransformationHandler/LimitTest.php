<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Limit;

class LimitTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Limit
     */
    protected $limit;

    public function setUp()
    {
        parent::setUp();
        $this->limit = new Limit();
    }

    public function testLimitSimple()
    {
        $this->assertEquals(
            array('crop' => 'limit'),
            $this->limit->process($this->value, 'small')
        );
    }

    public function testLimitWithDimensions()
    {
        $this->assertEquals(
            array(
                'crop' => 'limit',
                'width' => 100,
                'height' => 200
            ),
            $this->limit->process($this->value, 'small', array(100, 200))
        );
    }
}
