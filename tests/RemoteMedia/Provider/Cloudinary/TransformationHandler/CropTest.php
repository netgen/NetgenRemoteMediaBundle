<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Crop;

class CropTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Crop
     */
    protected $crop;

    public function setUp()
    {
        parent::setUp();
        $this->crop = new Crop();
    }

    public function testCrop()
    {
        $this->assertEquals(
            [
                [
                    'x' => 10,
                    'y' => 10,
                    'width' => 300,
                    'height' => 200,
                    'crop' => 'crop',
                ],
            ],
            $this->crop->process($this->value, 'small')
        );
    }

    public function testCropVariationDoesNotExist()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->crop->process($this->value, 'large');
    }
}
