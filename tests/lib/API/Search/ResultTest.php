<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Search;

use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

final class ResultTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Search\Result::__construct
     * @covers \Netgen\RemoteMedia\API\Search\Result::getNextCursor
     * @covers \Netgen\RemoteMedia\API\Search\Result::getResources
     * @covers \Netgen\RemoteMedia\API\Search\Result::getTotalCount
     *
     * @dataProvider dataProvider
     */
    public function testConstructor(
        int $totalCount,
        ?string $nextCursor,
        array $resources
    ): void {
        $result = new Result($totalCount, $nextCursor, $resources);

        self::assertSame(
            $totalCount,
            $result->getTotalCount(),
        );

        self::assertSame(
            $nextCursor,
            $result->getNextCursor(),
        );

        self::assertSame(
            $resources,
            $result->getResources(),
        );
    }

    public function dataProvider(): array
    {
        return [
            [0, null, []],
            [
                5,
                null,
                [
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                ],
            ],
            [
                500,
                'sdjf90r9okjdspfo93h2',
                [
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                    new RemoteResource(),
                ],
            ],
        ];
    }
}
