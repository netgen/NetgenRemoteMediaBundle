<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Values\TimestampableTrait;
use PHPUnit\Framework\TestCase;

final class TimestampableTraitTest extends TestCase
{
    use TimestampableTrait;

    /**
     * @covers \Netgen\RemoteMedia\API\Values\TimestampableTrait::getCreatedAt
     * @covers \Netgen\RemoteMedia\API\Values\TimestampableTrait::getUpdatedAt
     * @covers \Netgen\RemoteMedia\API\Values\TimestampableTrait::setUpdatedAt
     * @covers \Netgen\RemoteMedia\API\Values\TimestampableTrait::updateTimestamps
     */
    public function test(): void
    {
        self::assertNull(
            $this->getCreatedAt(),
        );

        self::assertNull(
            $this->getUpdatedAt(),
        );

        $this->updateTimestamps();

        self::assertInstanceOf(
            DateTimeImmutable::class,
            $this->getCreatedAt(),
        );

        self::assertLessThanOrEqual(
            new DateTimeImmutable(),
            $this->getCreatedAt(),
        );

        self::assertInstanceOf(
            DateTimeImmutable::class,
            $this->getUpdatedAt(),
        );

        self::assertLessThanOrEqual(
            new DateTimeImmutable(),
            $this->getUpdatedAt(),
        );

        $dateTime = new DateTimeImmutable();

        $this->setUpdatedAt($dateTime);

        self::assertSame(
            $dateTime,
            $this->getUpdatedAt(),
        );
    }
}
