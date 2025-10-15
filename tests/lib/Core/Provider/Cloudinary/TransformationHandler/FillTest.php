<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fill;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Fill::class)]
final class FillTest extends TestCase
{
    protected Fill $fill;

    protected function setUp(): void
    {
        $this->fill = new Fill();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'fill'],
            $this->fill->process(),
        );
    }

    #[DataProvider('dataProvider')]
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
