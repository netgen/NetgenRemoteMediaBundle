<?php

namespace Netgen\Bundle\RemoteMediaBundle\Cache;

use Tedivm\StashBundle\Service\CacheItem;
use Tedivm\StashBundle\Service\CacheService;

final class CacheWrapper
{
    /** @var  CacheService */
    private $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
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

        $item = $this->cacheService->getItem($args);

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
            $key = implode('/', $key);
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
