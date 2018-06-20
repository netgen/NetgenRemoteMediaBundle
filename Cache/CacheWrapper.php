<?php

namespace Netgen\Bundle\RemoteMediaBundle\Cache;

use Stash\Interfaces\PoolInterface;
use Tedivm\StashBundle\Service\CacheItem;
use Tedivm\StashBundle\Service\CacheService;

final class CacheWrapper
{
    /** @var  PoolInterface */
    private $cacheService;

    public function __construct(PoolInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Remove slashes from start and end of keys, and for content replace it with _ to avoid issues for Stash.
     *
     * @param string $key
     * @return string
     */
    private function washKey($key)
    {
        return str_replace('/', '_', trim($key, '/'));
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            if (method_exists($this->cacheService, 'flush')) {
                return $this->cacheService->flush();
            } else {
                return $this->cacheService->clear();
            }
        }

        $item = $this->getItem($args);

        return $item->clear();
    }

    /**
     * @param $key
     *
     * @return \Stash\Interfaces\ItemInterface|CacheItem
     */
    public function getItem($key)
    {
        if (is_array($key)) {
            $key = implode('/', array_map([$this, 'washKey'], $key));
        }

        return $this->cacheService->getItem($key);
    }

    /**
     * @param CacheItem $item
     * @param $value
     * @param $ttl
     */
    public function saveItem(CacheItem $item, $value, $ttl)
    {
        if (method_exists($item, 'save')) {
            $item->set($value)->setTTL($ttl);
            $item->save();
        } else {
            $item->set($value, $ttl);
        }
    }
}
