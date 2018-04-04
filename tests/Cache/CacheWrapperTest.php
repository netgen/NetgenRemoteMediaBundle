<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Cache;

use Netgen\Bundle\RemoteMediaBundle\Cache\CacheWrapper;
use PHPUnit\Framework\TestCase;
use Tedivm\StashBundle\Service\CacheItem;
use Tedivm\StashBundle\Service\CacheService;

class CacheWrapperTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Cache\CacheWrapper
     */
    protected $cacheWrapper;

    public function setUp()
    {
        $this->cacheService = $this->createMock(CacheService::class);

        $this->cacheWrapper = new CacheWrapper($this->cacheService);
    }

    public function testClearNoArguments()
    {
        if (method_exists(CacheService::class, 'flush')) {
            $this->cacheService
                ->expects($this->once())
                ->method('flush');
        } else {
            $this->cacheService
                ->expects($this->once())
                ->method('clear');
        }

        $this->cacheWrapper->clear();
    }

    public function testClearSingleArgument()
    {
        $item = $this->createMock(CacheItem::class);

        $this->cacheService
            ->method('getItem')
            ->willReturn($item);

        $item->expects($this->once())
            ->method('clear');

        $this->cacheWrapper->clear('key');
    }

    public function testClearMultipleArguments()
    {
        $item = $this->createMock(CacheItem::class);

        $this->cacheService
            ->method('getItem')
            ->willReturn($item);

        $item->expects($this->once())
            ->method('clear');

        $this->cacheWrapper->clear('key', 'key2');
    }

    public function testSaveItem()
    {
        $item = $this->createMock(CacheItem::class);
        $ttl = 7200;

        if (method_exists(CacheItem::class, 'save')) {
            $item->expects($this->once())
                ->method('set')
                ->with('value');
            $item->expects($this->once())
                ->method('setTTL')
                ->with($ttl);
        } else {
            $item->expects($this->once())
                ->method('set')
                ->with('value', $ttl);
        }

        $this->cacheWrapper->saveItem($item, 'value', 7200);
    }
}
