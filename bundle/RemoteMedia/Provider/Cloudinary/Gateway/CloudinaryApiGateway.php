<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;

use Cloudinary;
use Cloudinary\Api;
use Cloudinary\Search;
use Cloudinary\Uploader;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;

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
     * Perform search.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query
     */
    public function search(Query $query): Result
    {
        $expression = $this->buildSearchExpression($query);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results($query->getLimit())
            ->with_field('context')
            ->with_field('tags');

        if ($query->getNextCursor() !== null) {
            $search->next_cursor($query->getNextCursor());
        }

        $response = $search->execute();

        return Result::fromResponse($response);
    }

    /**
     * Get results count for search query.
     *
     * @return int
     */
    public function searchCount(Query $query)
    {
        $expression = $this->buildSearchExpression($query);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results(0);

        $response = $search->execute();

        return Result::fromResponse($response)->getTotalCount();
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
     * Lists all available folders inside a given parent folder.
     *
     * @return array
     */
    public function listSubFolders(string $parentFolder)
    {
        return $this->cloudinaryApi->subfolders($parentFolder)->getArrayCopy()['folders'];
    }

    /**
     * Creates new folder in Cloudinary.
     */
    public function createFolder(string $path)
    {
        $this->cloudinaryApi->create_folder($path);
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
        $expression = sprintf('folder:%s/*', $folder);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results(0);

        $response = $search->execute();

        return $response['total_count'];
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
        try {
            return (array) $this->cloudinaryApi->resource($id, ['resource_type' => $type]);
        } catch (Cloudinary\Error $e) {
            return [];
        }
    }

    /**
     * Lists all available tags.
     *
     * @return array
     */
    public function listTags()
    {
        $options = [
            'max_results' => 500,
        ];

        $tags = [];
        do {
            $result = $this->cloudinaryApi->tags($options);
            $tags = array_merge($tags, $result['tags']);

            if (array_key_exists('next_cursor', $result)) {
                $options['next_cursor'] = $result['next_cursor'];
            }
        } while (array_key_exists('next_cursor', $result));

        return $tags;
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
        return $this->cloudinaryUploader->add_tag($tag, [$id], ['resource_type' => $type]);
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
        return $this->cloudinaryUploader->remove_tag($tag, [$id], ['resource_type' => $type]);
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
        return $this->cloudinaryUploader->remove_all_tags([$id], ['resource_type' => $type]);
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
        $options['resource_type'] = $type;

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
     * @param $type
     * @param $options
     *
     * @return string
     */
    public function getDownloadLink($id, $type, $options)
    {
        $options['resource_type'] = $type;

        return $this->cloudinary->cloudinary_url($id, $options);
    }

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     * @param $type
     */
    public function delete($id, $type)
    {
        $options = ['invalidate' => true];
        $options = ['resource_type' => $type];

        $this->cloudinaryUploader->destroy($id, $options);
    }

    private function buildSearchExpression(Query $query)
    {
        $expressions = [];

        if ($query->getResourceType()) {
            $expressions[] = sprintf('resource_type:%s', $query->getResourceType());
        }

        if ($query->getQuery() !== '') {
            $expressions[] = sprintf('%s*', $query->getQuery());
        }

        if ($query->getTag()) {
            $expressions[] = sprintf('tags:%s', $query->getTag());
        }

        if ($query->getFolder() !== null) {
            $expressions[] = sprintf('folder:"%s"', $query->getFolder());
        }

        $resourceIds = $query->getResourceIds();
        if (count($resourceIds) > 0) {
            $resourceIds = array_map(function ($value) {
                return sprintf('public_id:"%s"', $value);
            }, $resourceIds);

            $expressions[] = '(' . implode(' OR ', $resourceIds) . ')';
        }

        return implode(' AND ', $expressions);
    }
}
