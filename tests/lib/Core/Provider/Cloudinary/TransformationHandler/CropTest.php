<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class CropTest extends TestCase
{
    private Crop $crop;

    protected function setUp(): void
    {
        $this->crop = new Crop();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop::process
     * @dataProvider validDataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->crop->process($config),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop::process
     * @dataProvider invalidDataProvider
     */
    public function testWithException(array $config): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->crop->process($config);
    }

    public function validDataProvider(): array
    {
        return [
            [
                [10, 10, 200, 300],
                [
                    'x' => 10,
                    'y' => 10,
                    'width' => 200,
                    'height' => 300,
                    'crop' => 'crop',
                ],
            ],
            [
                [5, 15, 500, 100],
                [
                    'x' => 5,
                    'y' => 15,
                    'width' => 500,
                    'height' => 100,
                    'crop' => 'crop',
                ],
            ],
            [
                [5, 15, 500, 100, 300, 400, 5, 2],
                [
                    'x' => 5,
                    'y' => 15,
                    'width' => 500,
                    'height' => 100,
                    'crop' => 'crop',
                ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            [
                [10, 10],
            ],
            [
                [5],
            ],
            [
                [5, 6, 7],
            ],
        ];
    }
}
