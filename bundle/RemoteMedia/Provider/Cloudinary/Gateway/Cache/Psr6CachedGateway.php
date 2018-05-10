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

    private function getBaseTag()
    {
        $tagBase = [self::PROJECT_KEY, self::PROVIDER_KEY];

        return implode('-', $tagBase);
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

        $this->cache->invalidateTags([$this->getBaseTag()]);

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
        $searchCacheKey = implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, $query, implode('|', $options)]);
        $cacheItem = $this->cache->getItem($searchCacheKey);

        if ($cacheItem->isHit()) {
            $searchResult = $cacheItem->get();

            return array_slice($searchResult, $offset, $limit);
        }

        $searchResult = $this->gateway->search($query, $options, $limit);
        $cacheItem->set($searchResult);
        $cacheItem->expiresAfter($this->ttl);
        $cacheItem->tag([$this->getBaseTag()]); // @todo: figure out which other tags are needed here? some search tag?
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
        $cacheItem->tag([$this->getBaseTag()]); // @todo: figure out which other tags are needed here? some list tag?

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
        $cacheItem->tag([$this->getBaseTag()]); // @todo: figure out correct tags for this

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
        $cacheItem->tag([$this->getBaseTag()]); // @todo: figure out correct tags for this

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
        $cacheItem->tag([$this->getBaseTag()]); // @todo: figure out correct tags for this

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

        // @todo: figure out which tags to clear
        $this->cache->invalidateTags([]);
        //$this->cache->clear(self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id);

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

        // @todo: figure out which tags to clear
        $this->cache->invalidateTags([]);
        //$this->cache->clear(self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id);

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

        // @todo: figure out which tags to clear
        $this->cache->invalidateTags([]);
        $this->cache->clear(self::PROJECT_KEY, self::PROVIDER_KEY, self::RESOURCE_ID, $id);

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
        // @todo: some caches probably need to be invalidated here, right?

        return $this->gateway->delete($id);
    }
}
