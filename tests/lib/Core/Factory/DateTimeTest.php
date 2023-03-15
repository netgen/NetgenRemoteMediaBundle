<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Factory;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;
use Netgen\RemoteMedia\Core\Factory\DateTime as DateTimeFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeFactoryInterface::class)]
final class DateTimeTest extends TestCase
{
    private DateTimeFactoryInterface $dateTimeFactory;

    protected function setUp(): void
    {
        $this->dateTimeFactory = new DateTimeFactory();
    }

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
