<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Scale::class)]
final class ScaleTest extends TestCase
{
    protected Scale $scale;

    protected function setUp(): void
    {
        $this->scale = new Scale();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'scale'],
            $this->scale->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->scale->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'scale',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'scale',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'scale',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
