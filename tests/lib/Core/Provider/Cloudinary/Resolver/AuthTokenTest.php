<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Values\AuthToken as AuthTokenEntity;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken as AuthTokenResolver;
use PHPUnit\Framework\TestCase;

final class AuthTokenTest extends TestCase
{
    protected const ENCRYPTION_KEY = '38128319a3a49e1d589a31a217e1a3f8';

    protected AuthTokenResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AuthTokenResolver(self::ENCRYPTION_KEY);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken::resolve
     *
     * @dataProvider dataProvider
     */
    public function testResolve(AuthTokenEntity $token, array $options): void
    {
        self::assertSame(
            $options,
            $this->resolver->resolve($token),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken::hasEncryptionKey
     */
    public function testHasEncryptionKey(): void
    {
        self::assertTrue($this->resolver->hasEncryptionKey());
    }

    public function dataProvider(): array
    {
        return [
            [
                AuthTokenEntity::fromExpiresAt(new DateTimeImmutable('2023/1/1 10:10:15')),
                [
                    'secure' => true,
                    'sign_url' => true,
                    'auth_token' => [
                        'key' => self::ENCRYPTION_KEY,
                        'expiration' => (new DateTimeImmutable('2023/1/1 10:10:15'))->getTimestamp(),
                    ],
                ],
            ],
            [
                AuthTokenEntity::fromExpiresAt(new DateTimeImmutable('2023/1/1 10:10:15'))->setIpAddress('192.168.1.1'),
                [
                    'secure' => true,
                    'sign_url' => true,
                    'auth_token' => [
                        'key' => self::ENCRYPTION_KEY,
                        'expiration' => (new DateTimeImmutable('2023/1/1 10:10:15'))->getTimestamp(),
                        'ip' => '192.168.1.1',
                    ],
                ],
            ],
            [
                AuthTokenEntity::fromExpiresAt(new DateTimeImmutable('2023/1/5 10:10:15'))->setStartsAt(new DateTimeImmutable('2023/1/1 08:10:00')),
                [
                    'secure' => true,
                    'sign_url' => true,
                    'auth_token' => [
                        'key' => self::ENCRYPTION_KEY,
                        'start_time' => (new DateTimeImmutable('2023/1/1 08:10:00'))->getTimestamp(),
                        'expiration' => (new DateTimeImmutable('2023/1/5 10:10:15'))->getTimestamp(),
                    ],
                ],
            ],
            [
                AuthTokenEntity::fromPeriod(new DateTimeImmutable('2023/1/1 08:10:00'), new DateTimeImmutable('2023/1/5 10:10:15')),
                [
                    'secure' => true,
                    'sign_url' => true,
                    'auth_token' => [
                        'key' => self::ENCRYPTION_KEY,
                        'start_time' => (new DateTimeImmutable('2023/1/1 08:10:00'))->getTimestamp(),
                        'expiration' => (new DateTimeImmutable('2023/1/5 10:10:15'))->getTimestamp(),
                    ],
                ],
            ],
            [
                AuthTokenEntity::fromPeriod(
                    new DateTimeImmutable('2023/1/1 08:10:00'),
                    new DateTimeImmutable('2023/1/5 10:10:15'),
                )->setIpAddress('192.168.1.1'),
                [
                    'secure' => true,
                    'sign_url' => true,
                    'auth_token' => [
                        'key' => self::ENCRYPTION_KEY,
                        'start_time' => (new DateTimeImmutable('2023/1/1 08:10:00'))->getTimestamp(),
                        'expiration' => (new DateTimeImmutable('2023/1/5 10:10:15'))->getTimestamp(),
                        'ip' => '192.168.1.1',
                    ],
                ],
            ],
        ];
    }
}
