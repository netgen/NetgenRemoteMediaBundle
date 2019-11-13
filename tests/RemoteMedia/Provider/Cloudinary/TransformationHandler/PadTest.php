<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Pad;

class PadTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Pad
     */
    protected $pad;

    public function setUp()
    {
        parent::setUp();
        $this->pad = new Pad();
    }

    public function testPadSimple()
    {
        $this->assertEquals(
            ['crop' => 'pad'],
            $this->pad->process($this->value, 'small')
        );
    }

    public function testPadWithDimensions()
    {
        $this->assertEquals(
            [
                'crop' => 'pad',
                'width' => 100,
                'height' => 200,
            ],
            $this->pad->process($this->value, 'small', [100, 200])
        );
    }

    public function testPadWithDimensionsAndColour()
    {
        $this->assertEquals(
            [
                'crop' => 'pad',
                'width' => 100,
                'height' => 200,
                'background' => 'red',
            ],
            $this->pad->process($this->value, 'small', [100, 200, 'red'])
        );
    }
}
