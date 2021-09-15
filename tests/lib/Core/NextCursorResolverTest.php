<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\Core\NextCursorResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class NextCursorResolverTest extends TestCase
{
    private const CACHE_TTL = 3600;

    private const TEST_CACHE_KEY = 'ngremotemedia-cloudinary-nextcursor-test __ ble __ __ a _test$|image|15|_test_folder_|some tag|created_at=desc-30';

    private const TEST_CURSOR = 'k84jh71osdf355asder';

    private MockObject $cache;

    private NextCursorResolver $resolver;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(TagAwareAdapterInterface::class);

        $this->resolver = new NextCursorResolver($this->cache, self::CACHE_TTL);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::getCacheKey
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::resolve
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::washKey
     */
    public function testResolve(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects(self::once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItemMock
            ->expects(self::once())
            ->method('get')
            ->willReturn(self::TEST_CURSOR);

        self::assertSame(self::TEST_CURSOR, $this->resolver->resolve($this->getQuery(), 30));
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::getCacheKey
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::resolve
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::washKey
     */
    public function testResolveWithoutMatch(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects(self::once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't get cursor key for query: " . (string) $this->getQuery() . ' with offset: 30');

        $this->resolver->resolve($this->getQuery(), 30);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::getCacheKey
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::save
     * @covers \Netgen\RemoteMedia\Core\NextCursorResolver::washKey
     */
    public function testSave(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects(self::once())
            ->method('getItem')
            ->with(self::TEST_CACHE_KEY)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->expects(self::once())
            ->method('set')
            ->with(self::TEST_CURSOR);

        $cacheItemMock
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->resolver->save($this->getQuery(), 30, self::TEST_CURSOR);
    }

    private function getQuery(): Query
    {
        return new Query(
            'test {} ble () /\ a @test$',
            'image',
            15,
            '(test_folder)',
            'some tag',
        );
    }
}
