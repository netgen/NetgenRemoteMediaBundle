<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;

use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Uploader;
use Cloudinary\Search;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;

class CloudinaryApiGateway extends Gateway
{
    /**
     * @var \Cloudinary
     */
    protected $cloudinary;

    /**
     * @var \Cloudinary\Api
     */
    protected $cloudinaryApi;

    /**
     * @var \Cloudinary\Uploader
     */
    protected $cloudinaryUploader;

    protected $cloudinarySearch;

    /**
     * @var int
     */
    protected $internalLimit;

    /**
     * @param $cloudName
     * @param $apiKey
     * @param $apiSecret
     * @param bool $useSubdomains
     */
    public function initCloudinary($cloudName, $apiKey, $apiSecret, $useSubdomains = false)
    {
        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'cdn_subdomain' => $useSubdomains,
            ]
        );

        $this->cloudinaryUploader = new Uploader();
        $this->cloudinaryApi = new Api();
        $this->cloudinarySearch = new Search();
    }

    public function setServices(Cloudinary $cloudinary, Uploader $uploader, Api $api, Search $search)
    {
        $this->cloudinary = $cloudinary;
        $this->cloudinaryUploader = $uploader;
        $this->cloudinaryApi = $api;
        $this->cloudinarySearch = $search;
    }

    /**
     * @param int $interalLimit
     */
    public function setInternalLimit($interalLimit)
    {
        $this->internalLimit = $interalLimit;
    }

    /**
     * Uploads file to cloudinary.
     *
     * @param $fileUri
     * @param $options
     *
     * @return array
     */
    public function upload($fileUri, $options)
    {
        return $this->cloudinaryUploader->upload($fileUri, $options);
    }

    /**
     * Generates url to the media with provided options.
     *
     * @param $source
     * @param $options
     *
     * @return string
     */
    public function getVariationUrl($source, $options)
    {
        return cloudinary_url_internal($source, $options);
    }

    /**
     * Offset and limit are ignored here, as cloudinary API does not support pagination.
     * All search results will be returned here, and caching layer takes care of slicing
     * the result.
     * Max limit for this endpoint is 500.
     *
     * @see \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CachedGateway.php
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
        if (isset($options['SearchByTags']) && true === $options['SearchByTags']) {
            if (isset($options['resource_type'])) {
                return $this->searchByTags($query, $options['resource_type']);
            }

            return $this->searchByTags($query);
        }

        return $this->searchByPrefix($query, $options);
    }

    /**
     * Offset and limit are ignored here, as cloudinary API does not support
     * pagination. Everything is fetched here, and caching layer takes care of slicing
     * the result.
     * Max limit for this endpoint is 500.
     *
     * @see \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway\CachedGateway.php
     *
     * @param $type
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function listResources($type, $limit, $offset)
    {
        $options = [
            'tags' => true,
            'context' => true,
            'resource_type' => $type,
        ];
        $options['max_results'] = 500;

        $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

        $items = $resources['resources'];
        if ($this->internalLimit <= 500) {
            return !empty($items) ? $items : [];
        }

        while (!empty($resources['next_cursor'])) {
            $options['next_cursor'] = $resources['next_cursor'];
            $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

            if (!empty($resources['resources'])) {
                $items = array_merge($items, $resources['resources']);
            }
        }

        return !empty($items) ? $items : [];
    }

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public function listFolders()
    {
        return $this->cloudinaryApi->root_folders()->getArrayCopy()['folders'];
    }

    /**
     * Returns the overall resources usage on the cloudinary account.
     *
     * @return int
     */
    public function countResources()
    {
        $usage = $this->cloudinaryApi->usage();

        return $usage['resources'];
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
        $options = ['type' => 'upload', 'max_results' => 500];

        if (!empty($folder)) {
            $options['prefix'] = $folder;
        }

        $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

        $count = count($resources['resources']);

        while (!empty($resources['next_cursor'])) {
            $options['next_cursor'] = $resources['next_cursor'];
            $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

            $count += count($resources['resources']);
        }

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
        $options = array(
            'type' => 'upload'
        );

        try {
            return $this->cloudinaryUploader->explicit($id, $options);
        } catch (Cloudinary\Error $e) {
            return [];
        }
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
        return $this->cloudinaryUploader->add_tag($tag, [$id]);
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
        return $this->cloudinaryUploader->remove_tag($tag, [$id]);
    }

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $options
     */
    public function update($id, $options)
    {
        $this->cloudinaryApi->update($id, $options);
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
        return cl_video_thumbnail_path($id, $options);
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
        return cl_video_tag($id, $options);
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
        return $this->cloudinary->cloudinary_url($id, $options);
    }

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     */
    public function delete($id)
    {
        $options = array('invalidate' => true);
        $this->cloudinaryUploader->destroy($id, $options);
    }

    /**
     * Perform search by tags.
     *
     * @param $query
     * @param $resourceType
     *
     * @return array
     */
    protected function searchByTags($query, $resourceType = 'image')
    {
        $resources = $this->cloudinaryApi->resources_by_tag(
            $query,
            [
                'tags' => true,
                'context' => true,
                'resource_type' => $resourceType,
                'max_results' => 500,
            ]
        );

        if (empty($resources)) {
            return [];
        }

        $resources = $resources->getArrayCopy();

        $items = $resources['resources'];
        while (!empty($resources['next_cursor'])) {
            $apiOptions['next_cursor'] = $resources['next_cursor'];
            $resources = $this->cloudinaryApi->resources($apiOptions)->getArrayCopy();

            if (!empty($resources['resources'])) {
                $items = array_merge($items, $resources['resources']);
            }
        }

        return !empty($items) ? $items : [];
    }

    /**
     * Perform search by prefix.
     *
     * @param $query
     * @param $options
     *
     * @return array
     */
    protected function searchByPrefix($query, $options)
    {
        $apiOptions = [
            'prefix' => $query,
            'type' => isset($options['type']) ? $options['type'] : 'upload',
            'tags' => true,
            'max_results' => 500,
        ];
        if (isset($options['resource_type'])) {
            $apiOptions['resource_type'] = $options['resource_type'];
        }

        $resources = $this->cloudinaryApi->resources($apiOptions)->getArrayCopy();

        $items = $resources['resources'];
        while (!empty($resources['next_cursor'])) {
            $apiOptions['next_cursor'] = $resources['next_cursor'];
            $resources = $this->cloudinaryApi->resources($apiOptions)->getArrayCopy();

            if (!empty($resources['resources'])) {
                $items = array_merge($items, $resources['resources']);
            }
        }

        return !empty($items) ? $items : [];
    }
}
