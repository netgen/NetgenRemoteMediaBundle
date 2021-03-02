<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use RuntimeException;

class NextCursorResolver
{
    /**
     * @var string
     */
    const PROJECT_KEY = 'ngremotemedia';

    /**
     * @var string
     */
    const PROVIDER_KEY = 'cloudinary';

    /**
     * @var string
     */
    const NEXT_CURSOR = 'nextcursor';

    /**
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    protected $cache;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * NextCursorResolver constructor.
     *
     * @param int $ttl
     */
    public function __construct(TagAwareAdapterInterface $cache, $ttl = 7200)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query $query
     * @param int $offset
     *
     * @return string
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function resolve(Query $query, int $offset): string
    {
        $cacheKey = $this->getCacheKey($query, $offset);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        throw new RuntimeException("Can't get cursor key for query: ".(string) $query." with offset: $offset");
    }

    /**
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query $query
     * @param int $offset
     * @param string $cursor
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function save(Query $query, int $offset, string $cursor): void
    {
        $cacheKey = $this->getCacheKey($query, $offset);
        $cacheItem = $this->cache->getItem($cacheKey);

        $cacheItem->set($cursor);
        $cacheItem->expiresAfter($this->ttl);

        $this->cache->save($cacheItem);
    }

    private function getCacheKey(Query $query, int $offset): string
    {
        $queryVars = [
            $query->getQuery(),
            $query->getResourceType(),
            $query->getLimit(),
            $query->getFolder(),
            $query->getTag(),
            \http_build_query($query->getSortBy(), '', ',')
        ];

        return $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::NEXT_CURSOR, implode('|', $queryVars), $offset])
        );
    }

    private function washKey($key)
    {
        $forbiddenCharacters = ['{', '}', '(', ')', '/', '\\', '@'];
        foreach ($forbiddenCharacters as $char) {
            $key = \str_replace($char, '_', \trim($key, $char));
        }

        return $key;
    }
}
