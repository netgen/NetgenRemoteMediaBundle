<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class CropTest extends BaseTest
{
    protected Crop $crop;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crop = new Crop();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop::process
     */
    public function testCrop(): void
    {
        self::assertSame(
            [
                [
                    'x' => 10,
                    'y' => 10,
                    'width' => 300,
                    'height' => 200,
                    'crop' => 'crop',
                ],
            ],
            $this->crop->process($this->resource, 'small'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop::process
     */
    public function testCropVariationDoesNotExist(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->crop->process($this->resource, 'large');
    }
}
