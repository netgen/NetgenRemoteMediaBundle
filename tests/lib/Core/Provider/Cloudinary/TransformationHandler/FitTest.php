<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit;
use PHPUnit\Framework\TestCase;

final class FitTest extends TestCase
{
    protected Fit $fit;

    protected function setUp(): void
    {
        $this->fit = new Fit();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'fit'],
            $this->fit->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit::process
     *
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->fit->process($config),
        );
    }

    public function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'fit',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'fit',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'fit',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
