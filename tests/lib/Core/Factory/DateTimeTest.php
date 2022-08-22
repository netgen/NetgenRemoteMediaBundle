<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Factory;

use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;
use Netgen\RemoteMedia\Core\Factory\DateTime as DateTimeFactory;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class DateTimeTest extends TestCase
{
    private DateTimeFactoryInterface $dateTimeFactory;

    protected function setUp(): void
    {
        $this->dateTimeFactory = new DateTimeFactory();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Factory\DateTime::createCurrent
     */
    public function testCreateCurrent(): void
    {
        $current = $this->dateTimeFactory->createCurrent();

        self::assertInstanceOf(
            DateTimeImmutable::class,
            $current,
        );

        self::assertGreaterThanOrEqual(
            $current,
            new DateTimeImmutable('now'),
        );
    }
}
