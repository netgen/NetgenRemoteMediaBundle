<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\StatusData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusData::class)]
final class StatusDataTest extends TestCase
{
    public function test(): void
    {
        $data = [
            'plan' => 'Advanced',
            'api_rate_limit' => 1000,
            'resources' => 500,
            'variations' => 3000,
        ];

        $statusData = new StatusData($data);

        self::assertTrue(
            $statusData->has('plan'),
        );

        self::assertTrue(
            $statusData->has('api_rate_limit'),
        );

        self::assertTrue(
            $statusData->has('resources'),
        );

        self::assertTrue(
            $statusData->has('variations'),
        );

        self::assertSame(
            'Advanced',
            $statusData->get('plan'),
        );

        self::assertSame(
            1000,
            $statusData->get('api_rate_limit'),
        );

        self::assertSame(
            500,
            $statusData->get('resources'),
        );

        self::assertSame(
            3000,
            $statusData->get('variations'),
        );

        self::assertFalse(
            $statusData->has('credits'),
        );

        self::assertNull(
            $statusData->get('credits'),
        );

        $statusData->add('credits', 6000);

        self::assertTrue(
            $statusData->has('credits'),
        );

        self::assertSame(
            6000,
            $statusData->get('credits'),
        );

        $data['credits'] = 6000;

        self::assertSame(
            $data,
            $statusData->all(),
        );
    }
}
