<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Variation;
use PHPUnit\Framework\TestCase;
use function json_encode;

final class VariationTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\Variation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\Variation::__toString
     */
    public function testConstructionWithParameters(): void
    {
        $parameters = [
            'url' => 'test/path/image.jpg',
            'width' => 150,
            'height' => 200,
            'coords' => [
                'x' => 20,
                'y' => 30,
            ],
        ];

        $variation = new Variation($parameters);

        self::assertSame(json_encode($parameters), (string) $variation);
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\Variation::__construct
     * @covers \Netgen\RemoteMedia\API\Values\Variation::__toString
     */
    public function testConstructionWithoutParameters(): void
    {
        $expectedResponseArray = [
            'url' => null,
            'width' => 0,
            'height' => 0,
            'coords' => [
                'x' => 0,
                'y' => 0,
            ],
        ];

        $variation = new Variation();

        self::assertSame(json_encode($expectedResponseArray), (string) $variation);
    }
}
