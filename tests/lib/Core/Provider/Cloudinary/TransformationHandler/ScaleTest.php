<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale;
use PHPUnit\Framework\TestCase;

final class ScaleTest extends TestCase
{
    protected Scale $scale;

    protected function setUp(): void
    {
        $this->scale = new Scale();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'scale'],
            $this->scale->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Scale::process
     *
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->scale->process($config),
        );
    }

    public function dataProvider(): array
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
