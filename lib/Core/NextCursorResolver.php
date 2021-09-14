<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Netgen\RemoteMedia\API\NextCursorResolverInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Query;
use RuntimeException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use function http_build_query;
use function implode;
use function is_array;
use function str_replace;
use function trim;

final class NextCursorResolver implements NextCursorResolverInterface
{
    public const PROJECT_KEY = 'ngremotemedia';
    public const PROVIDER_KEY = 'cloudinary';
    public const NEXT_CURSOR = 'nextcursor';

    private TagAwareAdapterInterface $cache;

    private int $ttl;

    public function __construct(TagAwareAdapterInterface $cache, int $ttl = 7200)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function resolve(Query $query, int $offset): string
    {
        $cacheKey = $this->getCacheKey($query, $offset);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        throw new RuntimeException("Can't get cursor key for query: " . (string) $query . " with offset: {$offset}");
    }

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
            is_array($query->getResourceType()) ? implode(',', $query->getResourceType()) : $query->getResourceType(),
            $query->getLimit(),
            $query->getFolder(),
            $query->getTag(),
            http_build_query($query->getSortBy(), '', ','),
        ];

        return $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::NEXT_CURSOR, implode('|', $queryVars), $offset]),
        );
    }

    private function washKey(string $key): string
    {
        $forbiddenCharacters = ['{', '}', '(', ')', '/', '\\', '@'];
        foreach ($forbiddenCharacters as $char) {
            $key = str_replace($char, '_', trim($key, $char));
        }

        return $key;
    }
}
