<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use DateInterval;
use DateTimeImmutable;
use Netgen\RemoteMedia\API\Values\AuthToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthToken::class)]
final class AuthTokenTest extends TestCase
{
    public function testFromDuration(): void
    {
        $token = AuthToken::fromDuration(50);

        self::assertNull($token->getStartsAt());
        self::assertInstanceOf(DateTimeImmutable::class, $token->getExpiresAt());
        self::assertSame((new DateTimeImmutable())->format('d.m.d/m/y H'), $token->getExpiresAt()->format('d.m.d/m/y H'));
        self::assertNull($token->getIpAddress());
        self::assertTrue($token->isValid());
    }

    public function testFromExpiredDuration(): void
    {
        $token = AuthToken::fromDuration(0);

        self::assertNull($token->getStartsAt());
        self::assertInstanceOf(DateTimeImmutable::class, $token->getExpiresAt());
        self::assertSame((new DateTimeImmutable())->format('d.m.d/m/y H'), $token->getExpiresAt()->format('d.m.d/m/y H'));
        self::assertNull($token->getIpAddress());
        self::assertFalse($token->isValid());
    }

    public function testFromExpiresAtWithIp(): void
    {
        $dateTime = new DateTimeImmutable();
        $dateTime = $dateTime->add(new DateInterval('PT1H'));

        $token = AuthToken::fromExpiresAt($dateTime);
        $token->setIpAddress('127.0.0.1');

        self::assertNull($token->getStartsAt());
        self::assertSame($dateTime->format('d.m.d/m/y H:i:s'), $token->getExpiresAt()->format('d.m.d/m/y H:i:s'));
        self::assertSame('127.0.0.1', $token->getIpAddress());
        self::assertTrue($token->isValid());
    }

    public function testFromExpiresAtWithInvalidIp(): void
    {
        $dateTime = new DateTimeImmutable();
        $dateTime = $dateTime->add(new DateInterval('PT1H'));

        $token = AuthToken::fromExpiresAt($dateTime);
        $token->setIpAddress('127.0.0.1');

        self::assertNull($token->getStartsAt());
        self::assertSame($dateTime->format('d.m.d/m/y H:i:s'), $token->getExpiresAt()->format('d.m.d/m/y H:i:s'));
        self::assertSame('127.0.0.1', $token->getIpAddress());
        self::assertFalse($token->isValid('192.168.1.1'));
    }

    public function testFromPeriod(): void
    {
        $startDateTime = new DateTimeImmutable();
        $startDateTime = $startDateTime->sub(new DateInterval('PT1H'));

        $endDateTime = new DateTimeImmutable();
        $endDateTime = $endDateTime->add(new DateInterval('PT1H'));

        $token = AuthToken::fromPeriod($startDateTime, $endDateTime);

        self::assertSame($startDateTime->format('d.m.d/m/y H'), $token->getStartsAt()->format('d.m.d/m/y H'));
        self::assertSame($endDateTime->format('d.m.d/m/y H'), $token->getExpiresAt()->format('d.m.d/m/y H'));
        self::assertNull($token->getIpAddress());
        self::assertTrue($token->isValid());
    }

    public function testFromFuturePeriod(): void
    {
        $startDateTime = new DateTimeImmutable();
        $startDateTime = $startDateTime->add(new DateInterval('PT1H'));

        $endDateTime = new DateTimeImmutable();
        $endDateTime = $endDateTime->add(new DateInterval('PT2H'));

        $token = AuthToken::fromExpiresAt($endDateTime);
        $token->setStartsAt($startDateTime);

        self::assertSame($startDateTime->format('d.m.d/m/y H'), $token->getStartsAt()->format('d.m.d/m/y H'));
        self::assertSame($endDateTime->format('d.m.d/m/y H'), $token->getExpiresAt()->format('d.m.d/m/y H'));
        self::assertNull($token->getIpAddress());
        self::assertFalse($token->isValid());
    }
}
