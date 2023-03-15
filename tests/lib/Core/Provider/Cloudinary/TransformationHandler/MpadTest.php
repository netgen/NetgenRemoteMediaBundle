<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mpad::class)]
final class MpadTest extends TestCase
{
    protected Mpad $mpad;

    protected function setUp(): void
    {
        $this->mpad = new Mpad();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'mpad'],
            $this->mpad->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->mpad->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'mpad',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'mpad',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'mpad',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
