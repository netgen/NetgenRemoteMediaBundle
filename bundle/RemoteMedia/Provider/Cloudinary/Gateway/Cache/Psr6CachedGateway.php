<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\Cache;

use Netgen\Bundle\RemoteMediaBundle\Cache\CacheWrapper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class Psr6CachedGateway extends Gateway
{
    const PROJECT_KEY = 'ngremotemedia';
    const PROVIDER_KEY = 'cloudinary';
    const SEARCH = 'search';
    const LIST = 'resource_list';
    const FOLDER_LIST = 'folder_list';
    const COUNT = 'resources_count';
    const FOLDER_COUNT = 'folder_count';
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
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway $gateway
     * @param TagAwareAdapterInterface $cache
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

        return implode('-', $tagBase);
    }

    private function getCacheTags($type)
    {
        $tags = [
            $this->getBaseTag(),
            self::PROJECT_KEY . '-' . self::PROVIDER_KEY . '-' . $type,
        ];

        return $tags;
    }

    private function getItemCacheTags($resourceId)
    {
        $tags = [
            $this->getBaseTag(),
            self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $resourceId
        ];

        return $tags;
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

        $tags = [
            $searchTags = $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_LIST),
            $this->getCacheTags(self::FOLDER_COUNT)
        ];
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
     *
     * @param string $query
     * @param array $options
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function search($query, $options = [], $limit = 10, $offset = 0)
    {
        $searchCacheKey = implode('-',
            [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, $query, implode('|', $options)]);
        $cacheItem = $this->cache->getItem($searchCacheKey);

        if ($cacheItem->isHit()) {
            $searchResult = $cacheItem->get();

            return array_slice($searchResult, $offset, $limit);
        }

        $searchResult = $this->gateway->search($query, $options, $limit);
        $cacheItem->set($searchResult);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::SEARCH));
        $this->cache->save($cacheItem);

        return array_slice($searchResult, $offset, $limit);
    }

    /**
     * List all available resources.
     *
     * @param $type
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function listResources($type, $limit, $offset)
    {
        $listCacheKey = implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::LIST, $type]);
        $cacheItem = $this->cache->getItem($listCacheKey);

        if ($cacheItem->isHit()) {
            $list = $cacheItem->get();

            return array_slice($list, $offset, $limit);
        }

        $list = $this->gateway->listResources($type, $limit, $offset);
        $cacheItem->set($list);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag($this->getCacheTags(self::LIST));

        return array_slice($list, $offset, $limit);
    }

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public function listFolders()
    {
        $listFolderCacheKey = implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST]);
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
        $countCacheKey = implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder]);
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
        $resourceCacheKey = implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id, $type]);
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
     * Adds new tag to the remote resource.
     *
     * @param $id
     * @param $tag
     *
     * @return array
     */
    public function addTag($id, $tag)
    {
        $value = $this->gateway->addTag($id, $tag);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }

    /**
     * Removes the tag from the remote resource.
     *
     * @param $id
     * @param $tag
     *
     * @return array
     */
    public function removeTag($id, $tag)
    {
        $value = $this->gateway->removeTag($id, $tag);

        $this->cache->invalidateTags($this->getItemCacheTags($id));

        return $value;
    }

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $options
     */
    public function update($id, $options)
    {
        $value = $this->gateway->update($id, $options);

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
     * @param $options
     *
     * @return string
     */
    public function getDownloadLink($id, $options)
    {
        return $this->gateway->getDownloadLink($id, $options);
    }

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     */
    public function delete($id)
    {
        $result = $this->gateway->delete($id);

        $tags = [
            $searchTags = $this->getCacheTags(self::SEARCH),
            $this->getCacheTags(self::LIST),
            $this->getCacheTags(self::FOLDER_COUNT)
        ];
        $this->cache->invalidateTags($tags);

        return $result;
    }
}
