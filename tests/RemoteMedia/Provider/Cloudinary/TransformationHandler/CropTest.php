<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;

class CropTest extends BaseTest
{
    /**
     * @var \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop
     */
    protected $crop;

    protected function setUp()
    {
        parent::setUp();
        $this->crop = new Crop();
    }

    public function testCrop()
    {
        self::assertEquals(
            [
                [
                    'x' => 10,
                    'y' => 10,
                    'width' => 300,
                    'height' => 200,
                    'crop' => 'crop',
                ],
            ],
            $this->crop->process($this->value, 'small'),
        );
    }

    public function testCropVariationDoesNotExist()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->crop->process($this->value, 'large');
    }
}
