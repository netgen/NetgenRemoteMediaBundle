<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill;
use PHPUnit\Framework\TestCase;

final class LfillTest extends TestCase
{
    protected Lfill $lfill;

    protected function setUp(): void
    {
        $this->lfill = new Lfill();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill::process
     */
    public function testWithoutConfig(): void
    {
        self::assertSame(
            ['crop' => 'lfill'],
            $this->lfill->process(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill::process
     *
     * @dataProvider dataProvider
     */
    public function test(array $config, array $result): void
    {
        self::assertSame(
            $result,
            $this->lfill->process($config),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                [],
                [
                    'crop' => 'lfill',
                ],
            ],
            [
                [100],
                [
                    'crop' => 'lfill',
                    'width' => 100,
                ],
            ],
            [
                [100, 200],
                [
                    'crop' => 'lfill',
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ];
    }
}
