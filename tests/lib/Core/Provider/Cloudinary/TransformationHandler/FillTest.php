<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill;
use PHPUnit\Framework\TestCase;

final class FillTest extends TestCase
{
    protected Fill $fill;

    protected function setUp(): void
    {
        $this->fill = new Fill();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'fill'],
            $this->fill->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill::process
     *
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->fill->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'fill',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'fill',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'fill',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
