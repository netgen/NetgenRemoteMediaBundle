<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\Cache;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class Psr6CachedGateway extends Gateway
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
    const SEARCH = 'search';
    /**
     * @var string
     */
    const SEARCH_COUNT = 'search_count';
    /**
     * @var string
     */
    const LIST = 'resource_list';
    /**
     * @var string
     */
    const FOLDER_LIST = 'folder_list';
    /**
     * @var string
     */
    const TAG_LIST = 'tag_list';
    /**
     * @var string
     */
    const COUNT = 'resources_count';
    /**
     * @var string
     */
    const FOLDER_COUNT = 'folder_count';
    /**
     * @var string
     */
    const RESOURCE_ID = 'resource';

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway
     */
    protected $gateway;

    /**
     * @var TagAwareAdapterInterface
     */
    protected $cache;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * CachedGateway constructor.
     *
     * @param int $ttl
     */
    public function __construct(Gateway $gateway, TagAwareAdapterInterface $cache, $ttl = 7200)
    {
        $this->gateway = $gateway;
        $this->cache = $cache;

        $this->ttl = $ttl;
    }

    /**
     * All items will be tagged with this tag, so all cache can be invalidate at once.
     *
     * @return string
     */
    private function getBaseTag()
    {
        $tagBase = [self::PROJECT_KEY, self::PROVIDER_KEY];

        return \implode('-', $tagBase);
    }

    private function getCacheTags($type)
    {
        return [
            $this->getBaseTag(),
            self::PROJECT_KEY . '-' . self::PROVIDER_KEY . '-' . $type,
        ];
    }

    private function getItemCacheTags($resourceId)
    {
        $tags = [
            $this->getBaseTag(),
            self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $resourceId,
        ];

        \array_walk($tags, function (&$tag) {
            $tag = $this->washKey($tag);
        });

        return $tags;
    }

    private function washKey($key)
    {
        $forbiddenCharacters = ['{', '}', '(', ')', '/', '\\', '@'];
        foreach ($forbiddenCharacters as $char) {
            $key = \str_replace($char, '_', \trim($key, $char));
        }

        return $key;
    }

    /**
     * Uploads file to cloudinary.
     *
     * @param string $fileUri
     * @param array $options
     *
     * @return array
     */
    public function upload($fileUri, $options)
    {
        $uploadResult = $this->gateway->upload($fileUri, $options);

        $tags = \array_unique(\array_merge(
            $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_LIST),
            $this->getCacheTags(self::FOLDER_COUNT)
        ));

        $this->cache->invalidateTags($tags);

        return $uploadResult;
    }

    /**
     * Generates url to the media with provided options.
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    public function getVariationUrl($source, $options)
    {
        return $this->gateway->getVariationUrl($source, $options);
    }

    /**
     * Perform search.
     */
    public function search(Query $query): Result
    {
        $searchCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, (string) $query])
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

    /**
     * Get results count for search query.
     *
     * @return int
     */
    public function searchCount(Query $query)
    {
        $searchCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH_COUNT, (string) $query])
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

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public function listFolders()
    {
        $listFolderCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST])
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

    /**
     * Lists all available folders inside a given parent folder.
     *
     * @return array
     */
    public function listSubFolders(string $parentFolder)
    {
        $listFolderCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST, $parentFolder])
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

    /**
     * Creates new folder in Cloudinary.
     */
    public function createFolder(string $path)
    {
        $this->gateway->createFolder($path);
    }

    /**
     * Returns the overall resources usage on the cloudinary account.
     *
     * @return int
     */
    public function countResources()
    {
        return $this->gateway->countResources();
    }

    /**
     * Returns the number of resources in the provided folder.
     *
     * @param $folder
     *
     * @return int
     */
    public function countResourcesInFolder($folder)
    {
        $countCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder])
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

    /**
     * Fetches the remote resource by id.
     *
     * @param $id
     * @param $type
     *
     * @return array
     */
    public function get($id, $type)
    {
        $resourceCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id, $type])
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

    /**
     * Lists all available tags.
     *
     * @return array
     */
    public function listTags()
    {
        $listTagCacheKey = $this->washKey(
            \implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::TAG_LIST])
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

    /**
     * Adds new tag to the remote resource.
     *
     * @param $id
     * @param $type
     * @param $tag
     *
     * @return array
     */
    public function addTag($id, $type, $tag)
    {
        $value = $this->gateway->addTag($id, $type, $tag);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }

    /**
     * Removes the tag from the remote resource.
     *
     * @param $id
     * @param $type
     * @param $tag
     *
     * @return array
     */
    public function removeTag($id, $type, $tag)
    {
        $value = $this->gateway->removeTag($id, $type, $tag);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }

    /**
     * Removes all tags from the remote resource.
     *
     * @param $id
     * @param $type
     *
     * @return array
     */
    public function removeAllTags($id, $type)
    {
        $value = $this->gateway->removeAllTags($id, $type);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }


    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $type
     * @param $options
     */
    public function update($id, $type, $options)
    {
        $value = $this->gateway->update($id, $type, $options);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }

    /**
     * Returns the url for the thumbnail of video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    public function getVideoThumbnail($id, $options = [])
    {
        return $this->gateway->getVideoThumbnail($id, $options);
    }

    /**
     * Generates video tag for the video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    public function getVideoTag($id, $options = [])
    {
        return $this->gateway->getVideoTag($id, $options);
    }

    /**
     * Generates download link for the remote resource.
     *
     * @param $id
     * @param $type
     * @param $options
     *
     * @return string
     */
    public function getDownloadLink($id, $type, $options)
    {
        return $this->gateway->getDownloadLink($id, $type, $options);
    }

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     * @param $type
     */
    public function delete($id, $type)
    {
        $result = $this->gateway->delete($id, $type);

        $tags = \array_unique(\array_merge(
            $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_COUNT)
        ));

        $this->cache->invalidateTags($tags);

        return $result;
    }
}
