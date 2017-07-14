<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use Tedivm\StashBundle\Service\CacheService;

class CachedGateway extends Gateway
{
    const PROJECT_KEY = 'ngremotemedia';
    const PROVIDER_KEY = 'cloudinary';
    const SEARCH = 'search';
    const LIST = 'resource_list';
    const FOLDER_LIST = 'folder_list';
    const COUNT = 'resources_count';
    const FOLDER_COUNT = 'folder_count';

    const TTL = 7200;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway
     */
    protected $gateway;

    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * CachedGateway constructor.
     * @param Gateway $gateway
     * @param CacheService $cache
     */
    public function __construct(Gateway $gateway, CacheService $cache)
    {
        $this->gateway = $gateway;
        $this->cache = $cache;
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

        $this->cache->clear(self::PROJECT_KEY, self::PROVIDER_KEY);

        return $uploadResult;
    }

    /**
     * Generates url to the media with provided options
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
    public function search($query, $options = array(), $limit = 10, $offset = 0)
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, $query, implode( '|', $options));
        $searchResult = $cache->get();
        if ($cache->isMiss()) {
            $searchResult = $this->gateway->search($query, $options, $limit);
            $cache->set($searchResult, self::TTL);
        }

        return array_slice($searchResult, $offset, $limit);
    }

    /**
     * List all available resources.
     *
     * @param $options
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    public function listResources($options, $offset, $limit)
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::LIST);

        $list = $cache->get();
        if ($cache->isMiss()) {
            $list = $this->gateway->listResources($options, $offset, $limit);
            $cache->set($list, self::TTL);
        }

        return array_slice($list, $offset, $limit);
    }

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public function listFolders()
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST);

        $list = $cache->get();
        if ($cache->isMiss()) {
            $list = $this->gateway->listFolders();
            $cache->set($list, self::TTL);
        }

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
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder);

        $count = $cache->get();
        if ($cache->isMiss()) {
            $count = $this->gateway->countResourcesInFolder($folder);
            $cache->set($count, self::TTL);
        }

        return $count;
    }

    /**
     * Fetches the remote resource by id.
     *
     * @param $id
     * @param $options
     *
     * @return array
     */
    public function get($id, $options)
    {
        return $this->gateway->get($id, $options);
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
        return $this->gateway->addTag($id, $tag);
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
        return $this->gateway->removeTag($id, $tag);
    }

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $options
     */
    public function update($id, $options)
    {
        return $this->gateway->update($id, $options);
    }

    /**
     * Returns the url for the thumbnail of video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    public function getVideoThumbnail($id, $options = array())
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
    public function getVideoTag($id, $options = array())
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
        return $this->gateway->delete($id);
    }
}
