<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\WatermarkText;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class WatermarkTextTest extends TestCase
{
    private WatermarkText $watermarkText;

    protected function setUp(): void
    {
        $this->watermarkText = new WatermarkText();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\WatermarkText::process
     *
     * @dataProvider validDataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->watermarkText->process($config),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\WatermarkText::process
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalid(array $config): void
    {
        self::expectException(TransformationHandlerFailedException::class);
        self::expectExceptionMessage('Transformation handler "Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\WatermarkText" identifier failed.');

        $this->watermarkText->process($config);
    }

    public function validDataProvider(): array
    {
        return [
            [
                ['text' => 'Some sample text'],
                [
                    'overlay' => [
                        'text' => 'Some sample text',
                        'font_family' => 'Arial',
                        'font_size' => 14,
                    ],
                ],
            ],
            [
                [
                    'text' => 'Test',
                    'test' => 'Some test',
                    'anything' => 50,
                    'color' => 'red',
                    'font_family' => 'Helvetica',
                    'font_size' => 20,
                    'align' => 'left',
                    'x' => 40,
                    'y' => 20,
                    'angle' => -90,
                    'opacity' => 80,
                    'density' => 200,
                ],
                [
                    'overlay' => [
                        'text' => 'Test',
                        'font_family' => 'Helvetica',
                        'font_size' => 20,
                    ],
                    'gravity' => 'west',
                    'color' => 'red',
                    'x' => 40,
                    'y' => 20,
                    'angle' => -90,
                    'opacity' => 80,
                    'density' => 200,
                ],
            ],
            [
                [
                    'text' => 'Some sample text',
                    'align' => 'right',
                ],
                [
                    'overlay' => [
                        'text' => 'Some sample text',
                        'font_family' => 'Arial',
                        'font_size' => 14,
                    ],
                    'gravity' => 'east',
                ],
            ],
            [
                [
                    'text' => 'Some sample text',
                    'align' => 'top',
                ],
                [
                    'overlay' => [
                        'text' => 'Some sample text',
                        'font_family' => 'Arial',
                        'font_size' => 14,
                    ],
                    'gravity' => 'north',
                ],
            ],
            [
                [
                    'text' => 'Some sample text',
                    'align' => 'bottom',
                ],
                [
                    'overlay' => [
                        'text' => 'Some sample text',
                        'font_family' => 'Arial',
                        'font_size' => 14,
                    ],
                    'gravity' => 'south',
                ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    'test' => 'Some test',
                    'anything' => 50,
                    'color' => 'red',
                    'font_family' => 'Helvetica',
                    'font_size' => 20,
                ],
            ],
        ];
    }
}
