<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize;

class ResizeTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize
     */
    protected $resize;

    public function setUp()
    {
        parent::setUp();
        $this->resize = new Resize();
    }

    public function testResizeWithDimensions()
    {
        $this->assertEquals(
            array(
                'width' => 100,
                'height' => 200
            ),
            $this->resize->process($this->value, 'small', array(100, 200))
        );
    }

    public function testMissingResizeConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->resize->process($this->value, 'named');
    }
}