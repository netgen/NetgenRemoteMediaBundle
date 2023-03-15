<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CacheableGatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

use function array_merge;
use function array_search;
use function array_unique;
use function array_values;
use function array_walk;
use function implode;
use function str_replace;
use function trim;

final class Psr6CachedGateway implements CacheableGatewayInterface
{
    public const PROJECT_KEY = 'ngremotemedia';
    public const PROVIDER_KEY = 'cloudinary';
    public const SEARCH = 'search';
    public const SEARCH_COUNT = 'search_count';
    public const FOLDER_LIST = 'folder_list';
    public const TAG_LIST = 'tag_list';
    public const COUNT = 'resources_count';
    public const FOLDER_COUNT = 'folder_count';
    public const RESOURCE_ID = 'resource';

    public function __construct(
        private GatewayInterface $gateway,
        private CacheItemPoolInterface $cache,
        private int $ttl = 7200
    ) {
    }

    public function usage(): StatusData
    {
        return $this->gateway->usage();
    }

    public function isEncryptionEnabled(): bool
    {
        return $this->gateway->isEncryptionEnabled();
    }

    public function countResources(): int
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::COUNT]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $count = $this->gateway->countResources();
        $cacheItem->set($count);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::COUNT));
        }

        $this->cache->save($cacheItem);

        return $count;
    }

    public function countResourcesInFolder(string $folder): int
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $count = $this->gateway->countResourcesInFolder($folder);
        $cacheItem->set($count);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::FOLDER_COUNT));
        }

        $this->cache->save($cacheItem);

        return $count;
    }

    public function listFolders(): array
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listFolders();
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::FOLDER_LIST));
        }

        $this->cache->save($cacheItem);

        return $list;
    }

    public function listSubFolders(string $parentFolder): array
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST, $parentFolder]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listSubFolders($parentFolder);
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::FOLDER_LIST));
        }

        $this->cache->save($cacheItem);

        return $list;
    }

    public function createFolder(string $path): void
    {
        $this->gateway->createFolder($path);
        $this->invalidateFoldersCache();
    }

    public function get(CloudinaryRemoteId $remoteId): RemoteResource
    {
        $cacheKey = $this->washKey(
            implode('-', [
                self::PROJECT_KEY,
                self::PROVIDER_KEY,
                self::RESOURCE_ID,
                $remoteId->getType(),
                $remoteId->getResourceType(),
                $remoteId->getResourceId(),
            ]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $resource = $this->gateway->get($remoteId);
        $cacheItem->set($resource);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getItemCacheTags(
                $remoteId->getType(),
                $remoteId->getResourceType(),
                $remoteId->getResourceId(),
            ));
        }

        $this->cache->save($cacheItem);

        return $resource;
    }

    public function upload(string $fileUri, array $options): RemoteResource
    {
        $uploadResult = $this->gateway->upload($fileUri, $options);

        $this->invalidateResourceCache(CloudinaryRemoteId::fromRemoteId($uploadResult->getRemoteId()));
        $this->invalidateResourceListCache();
        $this->invalidateFoldersCache();
        $this->invalidateTagsCache();

        return $uploadResult;
    }

    public function update(CloudinaryRemoteId $remoteId, array $options): void
    {
        $this->gateway->update($remoteId, $options);

        $this->invalidateResourceCache($remoteId);
        $this->invalidateTagsCache();
    }

    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void
    {
        $this->gateway->removeAllTagsFromResource($remoteId);

        $this->invalidateResourceCache($remoteId);
        $this->invalidateTagsCache();
    }

    public function delete(CloudinaryRemoteId $remoteId): void
    {
        $this->gateway->delete($remoteId);

        $this->invalidateResourceCache($remoteId);
        $this->invalidateResourceListCache();
    }

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token, array $transformations = []): string
    {
        return $this->gateway->getAuthenticatedUrl($remoteId, $token, $transformations);
    }

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations): string
    {
        return $this->gateway->getVariationUrl($remoteId, $transformations);
    }

    public function search(Query $query): Result
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, (string) $query]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $this->gateway->search($query);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::SEARCH));
        }

        $this->cache->save($cacheItem);

        return $result;
    }

    public function searchCount(Query $query): int
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH_COUNT, (string) $query]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $this->gateway->searchCount($query);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag(
                $this->getCacheTags(self::SEARCH_COUNT),
            );
        }

        $this->cache->save($cacheItem);

        return $result;
    }

    public function listTags(): array
    {
        $cacheKey = $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::TAG_LIST]),
        );

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $list = $this->gateway->listTags();
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);

        if ($this->isCacheTaggable()) {
            $cacheItem->tag($this->getCacheTags(self::TAG_LIST));
        }

        $this->cache->save($cacheItem);

        return $list;
    }

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        return $this->gateway->getVideoThumbnail($remoteId, $options);
    }

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        return $this->gateway->getImageTag($remoteId, $options);
    }

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        return $this->gateway->getVideoTag($remoteId, $options);
    }

    public function getDownloadLink(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        return $this->gateway->getDownloadLink($remoteId, $options);
    }

    public function invalidateResourceListCache(): void
    {
        if (!$this->isCacheTaggable()) {
            return;
        }

        $tags = array_values(
            array_unique(
                array_merge(
                    $this->getCacheTags(self::SEARCH),
                    $this->getCacheTags(self::SEARCH_COUNT),
                    $this->getCacheTags(self::COUNT),
                    $this->getCacheTags(self::TAG_LIST),
                ),
            ),
        );

        unset($tags[array_search($this->getBaseTag(), $tags, true)]);

        $this->cache->invalidateTags($tags);
    }

    public function invalidateResourceCache(CloudinaryRemoteId $remoteId): void
    {
        if (!$this->isCacheTaggable()) {
            return;
        }

        $tags = $this->getItemCacheTags(
            $remoteId->getType(),
            $remoteId->getResourceType(),
            $remoteId->getResourceId(),
        );

        unset($tags[array_search($this->getBaseTag(), $tags, true)]);

        $this->cache->invalidateTags($tags);
    }

    public function invalidateFoldersCache(): void
    {
        if (!$this->isCacheTaggable()) {
            return;
        }

        $tags = array_values(
            array_unique(
                array_merge(
                    $this->getCacheTags(self::FOLDER_LIST),
                    $this->getCacheTags(self::FOLDER_COUNT),
                ),
            ),
        );

        unset($tags[array_search($this->getBaseTag(), $tags, true)]);

        $this->cache->invalidateTags($tags);
    }

    public function invalidateTagsCache(): void
    {
        if (!$this->isCacheTaggable()) {
            return;
        }

        $tags = array_values(
            array_unique(
                array_merge(
                    $this->getCacheTags(self::TAG_LIST),
                ),
            ),
        );

        unset($tags[array_search($this->getBaseTag(), $tags, true)]);

        $this->cache->invalidateTags($tags);
    }

    private function isCacheTaggable(): bool
    {
        return $this->cache instanceof TagAwareAdapterInterface;
    }

    /**
     * All items will be tagged with this tag, so all cache can be invalidated at once.
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

    private function getItemCacheTags(string $type, string $resourceType, string $resourceId): array
    {
        $tags = [
            $this->getBaseTag(),
            self::PROJECT_KEY . '-' . self::PROVIDER_KEY . '-' . self::RESOURCE_ID . '-' . $type . '-' . $resourceType . '-' . $resourceId,
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
