<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class NextCursorResolverTest extends TestCase
{
    private const CACHE_TTL = 3600;

    private const TEST_CACHE_KEY = 'ngremotemedia-cloudinary-nextcursor-test __ ble __ __ a _test$|image|15|_test_folder_|some tag|created_at=desc-30';

    private const TEST_CURSOR = 'k84jh71osdf355asder';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $cache;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\NextCursorResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(TagAwareAdapterInterface::class);

        $this->resolver = new NextCursorResolver($this->cache, self::CACHE_TTL);
    }

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

        self::assertEquals(self::TEST_CURSOR, $this->resolver->resolve($this->getQuery(), 30));
    }

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
