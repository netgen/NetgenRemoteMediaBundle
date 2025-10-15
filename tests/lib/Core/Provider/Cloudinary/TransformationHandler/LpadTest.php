<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Lpad::class)]
final class LpadTest extends TestCase
{
    protected Lpad $lpad;

    protected function setUp(): void
    {
        $this->lpad = new Lpad();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'lpad'],
            $this->lpad->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->lpad->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'lpad',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'lpad',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'lpad',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
            [
                [300, 400, 'red'],
                [
                    'crop' => 'lpad',
                    'width' => 300,
                    'height' => 400,
                    'background' => 'red',
                ],
            ],
        ];
    }
}
