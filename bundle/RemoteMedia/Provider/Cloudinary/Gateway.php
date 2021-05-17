<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;

/**
 * @internal
 */
abstract class Gateway
{
    /**
     * Returns API rate limits information.
     *
     * @return mixed
     */
    abstract public function usage();

    /**
     * Uploads file to cloudinary.
     *
     * @param string $fileUri
     * @param array $options
     *
     * @return array
     */
    abstract public function upload($fileUri, $options);

    /**
     * Generates url to the media with provided options.
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    abstract public function getVariationUrl($source, $options);

    /**
     * Perform search.
     */
    abstract public function search(Query $query): Result;

    /**
     * Get results count for search query.
     *
     * @return int
     */
    abstract public function searchCount(Query $query);

    /**
     * Lists all available folders.
     *
     * @return array
     */
    abstract public function listFolders();

    /**
     * Lists all available folders inside a given parent folder.
     *
     * @return array
     */
    abstract public function listSubFolders(string $parentFolder);

    /**
     * Creates new folder in Cloudinary.
     */
    abstract public function createFolder(string $path);

    /**
     * Returns the overall resources usage on the cloudinary account.
     *
     * @return int
     */
    abstract public function countResources();

    /**
     * Returns the number of resources in the provided folder.
     *
     * @param $folder
     *
     * @return int
     */
    abstract public function countResourcesInFolder($folder);

    /**
     * Fetches the remote resource by id.
     *
     * @param $id
     * @param $type
     *
     * @return array
     */
    abstract public function get($id, $type);

    /**
     * Lists all available tags.
     *
     * @return array
     */
    abstract public function listTags();

    /**
     * Adds new tag to the remote resource.
     *
     * @param $id
     * @param $type
     * @param $tag
     *
     * @return array
     */
    abstract public function addTag($id, $type, $tag);

    /**
     * Removes the tag from the remote resource.
     *
     * @param $id
     * @param $type
     * @param $tag
     *
     * @return array
     */
    abstract public function removeTag($id, $type, $tag);

    /**
     * Removes all tags from the remote resource.
     *
     * @param $id
     * @param $type
     *
     * @return array
     */
    abstract public function removeAllTags($id, $type);

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $type
     * @param $options
     */
    abstract public function update($id, $type, $options);

    /**
     * Returns the url for the thumbnail of video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    abstract public function getVideoThumbnail($id, $options = []);

    /**
     * Generates video tag for the video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    abstract public function getVideoTag($id, $options = []);

    /**
     * Generates download link for the remote resource.
     *
     * @param $id
     * @param $type
     * @param $options
     *
     * @return string
     */
    abstract public function getDownloadLink($id, $type, $options);

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     * @param $type
     */
    abstract public function delete($id, $type);
}
