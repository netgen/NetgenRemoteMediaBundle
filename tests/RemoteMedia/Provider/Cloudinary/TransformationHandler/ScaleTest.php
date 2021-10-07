<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Scale;

class ScaleTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Scale
     */
    protected $scale;

    protected function setUp()
    {
        parent::setUp();
        $this->scale = new Scale();
    }

    public function testScaleSimple()
    {
        self::assertEquals(
            ['crop' => 'scale'],
            $this->scale->process($this->value, 'small')
        );
    }

    public function testScaleWithDimensions()
    {
        self::assertEquals(
            [
                'crop' => 'scale',
                'width' => 100,
                'height' => 200,
            ],
            $this->scale->process($this->value, 'small', [100, 200])
        );
    }
}
