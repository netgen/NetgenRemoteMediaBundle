<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad;
use PHPUnit\Framework\TestCase;

final class LpadTest extends TestCase
{
    protected Lpad $lpad;

    protected function setUp(): void
    {
        $this->lpad = new Lpad();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'lpad'],
            $this->lpad->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad::process
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->lpad->process($config),
        );
    }

    public function dataProvider(): array
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
