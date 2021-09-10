<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Query;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Result;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use function array_merge;
use function array_unique;
use function array_walk;
use function implode;
use function str_replace;
use function trim;

final class Psr6CachedGateway extends Gateway
{
    const PROJECT_KEY = 'ngremotemedia';
    const PROVIDER_KEY = 'cloudinary';
    const SEARCH = 'search';
    const SEARCH_COUNT = 'search_count';
    const LIST = 'resource_list';
    const FOLDER_LIST = 'folder_list';
    const TAG_LIST = 'tag_list';
    const COUNT = 'resources_count';
    const FOLDER_COUNT = 'folder_count';
    const RESOURCE_ID = 'resource';

    protected Gateway $gateway;

    protected TagAwareAdapterInterface $cache;

    protected int $ttl;

    public function __construct(Gateway $gateway, TagAwareAdapterInterface $cache, int $ttl = 7200)
    {
        $this->gateway = $gateway;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function usage(): array
    {
        return $this->gateway->usage();
    }

    public function upload(string $fileUri, array $options): array
    {
        $uploadResult = $this->gateway->upload($fileUri, $options);

        $tags = array_unique(array_merge(
            $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_LIST),
            $this->getCacheTags(self::FOLDER_COUNT),
        ));

        $this->cache->invalidateTags($tags);

        return $uploadResult;
    }

    public function getVariationUrl(string $source, array $options): string
    {
        return $this->gateway->getVariationUrl($source, $options);
    }

    public function search(Query $query): Result
    {
        $searchCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, (string) $query]),
        );

        $cacheItem = $this->cache->getItem($searchCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $this->gateway->search($query);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::SEARCH));
        $this->cache->save($cacheItem);

        return $result;
    }

    public function searchCount(Query $query): int
    {
        $searchCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH_COUNT, (string) $query]),
        );

        $cacheItem = $this->cache->getItem($searchCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $this->gateway->searchCount($query);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::SEARCH_COUNT));
        $this->cache->save($cacheItem);

        return $result;
    }

    public function listFolders(): array
    {
        $listFolderCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST]),
        );
        $cacheItem = $this->cache->getItem($listFolderCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listFolders();
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::FOLDER_LIST));

        return $list;
    }

    public function listSubFolders(string $parentFolder): array
    {
        $listFolderCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST, $parentFolder]),
        );
        $cacheItem = $this->cache->getItem($listFolderCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listSubFolders($parentFolder);
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::FOLDER_LIST));

        return $list;
    }

    public function createFolder(string $path): void
    {
        $this->gateway->createFolder($path);
    }

    public function countResources(): int
    {
        return $this->gateway->countResources();
    }

    public function countResourcesInFolder(string $folder): int
    {
        $countCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder]),
        );
        $cacheItem = $this->cache->getItem($countCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $count = $this->gateway->countResourcesInFolder($folder);
        $cacheItem->set($count);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::FOLDER_COUNT));

        return $count;
    }

    public function get(string $id, string $type): array
    {
        $resourceCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id, $type]),
        );
        $cacheItem = $this->cache->getItem($resourceCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $value = $this->gateway->get($id, $type);
        $cacheItem->set($value);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getItemCacheTags($id));

        return $value;
    }

    public function listTags(): array
    {
        $listTagCacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::TAG_LIST]),
        );
        $cacheItem = $this->cache->getItem($listTagCacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listTags();
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::TAG_LIST));

        return $list;
    }

    public function addTag(string $id, string $type, string $tag): void
    {
        $this->gateway->addTag($id, $type, $tag);
        $this->cache->invalidateTags($this->getItemCacheTags($id));
    }

    public function removeTag(string $id, string $type, string $tag): void
    {
        $this->gateway->removeTag($id, $type, $tag);
        $this->cache->invalidateTags($this->getItemCacheTags($id));
    }

    public function removeAllTags(string $id, string $type): void
    {
        $this->gateway->removeAllTags($id, $type);
        $this->cache->invalidateTags($this->getItemCacheTags($id));
    }

    public function update(string $id, string $type, array $options): void
    {
        $this->gateway->update($id, $type, $options);
        $this->cache->invalidateTags($this->getItemCacheTags($id));
    }

    public function getVideoThumbnail(string $id, array $options = []): string
    {
        return $this->gateway->getVideoThumbnail($id, $options);
    }

    public function getVideoTag(string $id, array $options = []): string
    {
        return $this->gateway->getVideoTag($id, $options);
    }

    public function getDownloadLink(string $id, string $type, array $options): string
    {
        return $this->gateway->getDownloadLink($id, $type, $options);
    }

    public function delete(string $id, string $type): void
    {
        $this->gateway->delete($id, $type);

        $tags = array_unique(array_merge(
            $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_COUNT),
        ));

        $this->cache->invalidateTags($tags);
    }

    /**
     * All items will be tagged with this tag, so all cache can be invalidate at once.
     */
    private function getBaseTag(): string
    {
        $tagBase = [self::PROJECT_KEY, self::PROVIDER_KEY];

        return implode('-', $tagBase);
    }

    private function getCacheTags(string $type): array
    {
        return [
            $this->getBaseTag(),
            self::PROJECT_KEY . '-' . self::PROVIDER_KEY . '-' . $type,
        ];
    }

    private function getItemCacheTags(string $resourceId): array
    {
        $tags = [
            $this->getBaseTag(),
            self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $resourceId,
        ];

        array_walk($tags, function (&$tag) {
            $tag = $this->washKey($tag);
        });

        return $tags;
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
