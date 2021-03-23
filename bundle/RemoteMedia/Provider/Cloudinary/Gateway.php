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
     * Lists all available folders.
     *
     * @return array
     */
    abstract public function listFolders();

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
     * @param $tag
     *
     * @return array
     */
    abstract public function addTag($id, $tag);

    /**
     * Removes the tag from the remote resource.
     *
     * @param $id
     * @param $tag
     *
     * @return array
     */
    abstract public function removeTag($id, $tag);

    /**
     * Removes all tags from the remote resource.
     *
     * @param $id
     *
     * @return array
     */
    abstract public function removeAllTags($id);

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $options
     */
    abstract public function update($id, $options);

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
     * @param $options
     *
     * @return string
     */
    abstract public function getDownloadLink($id, $options);

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     */
    abstract public function delete($id);
}
