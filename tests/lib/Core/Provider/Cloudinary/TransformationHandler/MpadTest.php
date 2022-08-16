<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad;
use PHPUnit\Framework\TestCase;

final class MpadTest extends TestCase
{
    protected Mpad $mpad;

    protected function setUp(): void
    {
        $this->mpad = new Mpad();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'mpad'],
            $this->mpad->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Mpad::process
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->mpad->process($config),
        );
    }

    public function dataProvider(): array
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
