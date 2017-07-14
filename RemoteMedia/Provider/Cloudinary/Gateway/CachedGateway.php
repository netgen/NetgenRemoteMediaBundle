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

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway
     */
    protected $gateway;

    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    public function __construct(Gateway $gateway, CacheService $cache)
    {
        $this->gateway = $gateway;
        $this->cache = $cache;
    }

    public function upload($fileUri, $options)
    {
        return $this->gateway->upload($fileUri, $options);
    }

    public function getVariationUrl($source, $options)
    {
        return $this->gateway->getVariationUrl($source, $options);
    }

    public function search($query, $options = array(), $limit = 10, $offset = 0)
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::SEARCH, $query, implode( '|', $options));
        $searchResult = $cache->get();
        if ($cache->isMiss()) {
            $searchResult = $this->gateway->search($query, $options, $limit);
            $cache->set($searchResult);
        }

        return array_slice($searchResult, $offset, $limit);
    }

    public function listResources($options, $offset, $limit)
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::LIST);

        $list = $cache->get();
        if ($cache->isMiss()) {
            $list = $this->gateway->listResources($options, $offset, $limit);
            $cache->set($list);
        }

        return array_slice($list, $offset, $limit);
    }

    public function listFolders()
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_LIST);

        $list = $cache->get();
        if ($cache->isMiss()) {
            $list = $this->gateway->listFolders();
            $cache->set($list);
        }

        return $list;
    }

    public function countResources()
    {
        return $this->gateway->countResources();
    }

    public function countResourcesInFolder($folder)
    {
        $cache = $this->cache->getItem(self::PROJECT_KEY, self::PROVIDER_KEY, self::FOLDER_COUNT, $folder);

        $count = $cache->get();
        if ($cache->isMiss()) {
            $count = $this->gateway->countResourcesInFolder($folder);
            $cache->set($count);
        }

        return $count;
    }

    public function get($id, $options)
    {
        return $this->gateway->get($id, $options);
    }

    public function addTag($id, $tag)
    {
        return $this->gateway->addTag($id, $tag);
    }

    public function removeTag($id, $tag)
    {
        return $this->gateway->removeTag($id, $tag);
    }

    public function update($id, $options)
    {
        return $this->gateway->update($id, $options);
    }

    public function getVideoThumbnail($id, $options = array())
    {
        return $this->gateway->getVideoThumbnail($id, $options);
    }

    public function getVideoTag($id, $options = array())
    {
        return $this->gateway->getVideoTag($id, $options);
    }

    public function getDownloadLink($id, $options)
    {
        return $this->gateway->getDownloadLink($id, $options);
    }

    public function delete($id)
    {
        return $this->gateway->delete($id);
    }
}
