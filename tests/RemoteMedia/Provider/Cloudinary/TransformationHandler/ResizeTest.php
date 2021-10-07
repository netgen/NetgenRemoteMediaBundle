<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize;

class ResizeTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize
     */
    protected $resize;

    protected function setUp()
    {
        parent::setUp();
        $this->resize = new Resize();
    }

    public function testResizeWithDimensions()
    {
        self::assertEquals(
            [
                'width' => 100,
                'height' => 200,
            ],
            $this->resize->process($this->value, 'small', [100, 200])
        );
    }

    public function testMissingResizeConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->resize->process($this->value, 'named');
    }
}
