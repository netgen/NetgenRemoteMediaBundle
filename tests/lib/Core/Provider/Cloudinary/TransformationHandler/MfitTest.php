<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mfit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mfit::class)]
final class MfitTest extends TestCase
{
    protected Mfit $mfit;

    protected function setUp(): void
    {
        $this->mfit = new Mfit();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'mfit'],
            $this->mfit->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->mfit->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'mfit',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'mfit',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'mfit',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
