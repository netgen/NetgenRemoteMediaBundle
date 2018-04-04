<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Mpad;

class MpadTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Mpad
     */
    protected $mpad;

    public function setUp()
    {
        parent::setUp();
        $this->mpad = new Mpad();
    }

    public function testMpadSimple()
    {
        $this->assertEquals(
            ['crop' => 'mpad'],
            $this->mpad->process($this->value, 'small')
        );
    }

    public function testMpadWithDimensions()
    {
        $this->assertEquals(
            [
                'crop' => 'mpad',
                'width' => 100,
                'height' => 200,
            ],
            $this->mpad->process($this->value, 'small', [100, 200])
        );
    }
}
