<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Limit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Limit::class)]
final class LimitTest extends TestCase
{
    protected Limit $limit;

    protected function setUp(): void
    {
        $this->limit = new Limit();
    }

    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'limit'],
            $this->limit->process(),
        );
    }

    #[DataProvider('dataProvider')]
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->limit->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'limit',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'limit',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'limit',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
