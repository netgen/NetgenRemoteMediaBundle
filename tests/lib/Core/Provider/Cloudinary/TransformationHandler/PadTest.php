<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Pad;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pad::class)]
final class PadTest extends TestCase
{
    protected Pad $pad;

    protected function setUp(): void
    {
        $this->pad = new Pad();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'pad'],
            $this->pad->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->pad->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'pad',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'pad',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'pad',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
            [
                [100, 200, 'red'],
                [
                    'crop' => 'pad',
                    'width' => 100,
                    'height' => 200,
                    'background' => 'red',
                ],
            ],
        ];
    }
}
