<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use \Cloudinary;
use \Cloudinary\Uploader;
use \Cloudinary\Api;
use \Cloudinary\Search;

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

    /**
     * @var \Cloudinary\Search
     */
    protected $cloudinarySearch;

    public function initCloudinary($cloudName, $apiKey, $apiSecret)
    {
        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            array(
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            )
        );

        $this->cloudinaryUploader = new Uploader();
        $this->cloudinaryApi = new Api();

        $this->cloudinarySearch = new Search();
    }

    public function upload($fileUri, $options)
    {
        return $this->cloudinaryUploader->upload($fileUri, $options);
    }

    public function getVariationUrl($source, $options, $video = false)
    {
        return cloudinary_url_internal($source, $options);
    }

    public function search($query, $options = array(), $limit = 10)
    {
        if ($options['SearchByTags']) {
            $result = $this->cloudinaryApi->resources_by_tag(
                $query,
                array(
                    'tags' => true,
                    'context' => true,
                )
            );
        } else {
            $result = $this->cloudinaryApi->resources(
                array(
                    'prefix' => $query,
                    'type' => $options['type'],
                    'tags' => true,
                    'max_results' => $limit
                )
            )->getArrayCopy();
        }

        if (!empty($result['resources'])) {
            return $result['resources'];
        }

        return array();
    }

    public function listResources($options)
    {
        $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

        if (!empty($resources['resources'])) {
            return $resources['resources'];
        }

        return array();
    }

    public function countResources()
    {
        $usage = $this->cloudinaryApi->usage();

        return $usage['resources'];
    }

    // @todo: check if more than 500
    public function countResourcesInFolder($folder)
    {
        $options = array('type' => 'upload', 'max_results' => 500);

        if (!empty($folder)) {
            $options['prefix'] = $folder;
        }

        $resources = $this->cloudinaryApi->resources($options)->getArrayCopy();

        return count($resources['resources']);
    }

    public function get($id, $options)
    {
        $response = $this->cloudinaryApi->resources_by_ids(
            array($id),
            $options
        )->getIterator()->current();

        return $response[0];
    }

    public function addTag($id, $tag)
    {
        return $this->cloudinaryUploader->add_tag($tag, array($id));
    }

    public function removeTag($id, $tag)
    {
        return $this->cloudinaryUploader->remove_tag($tag, array($id));
    }

    public function update($id, $options)
    {
        $this->cloudinaryApi->update($id, $options);
    }

    public function getVideoThumbnail($id, $options = array())
    {
        return cl_video_thumbnail_path($id, $options);
    }

    public function getVideoTag($id, $options = array())
    {
        return cl_video_tag($id, $options);
    }

    public function getDownloadLink($id, $options)
    {
        return $this->cloudinary->cloudinary_url($id, $options);
    }

    public function delete($id)
    {
        $this->cloudinaryApi->delete_resources(array($id));
    }

    public function listFolders()
    {
        return $this->cloudinaryApi->root_folders()->getArrayCopy()['folders'];
    }
}
