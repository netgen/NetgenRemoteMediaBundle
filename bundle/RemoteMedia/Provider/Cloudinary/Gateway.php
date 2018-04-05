<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

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
     *
     * @param string $query
     * @param array $options
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    abstract public function search($query, $options = [], $limit = 10, $offset = 0);

    /**
     * List all available resources.
     *
     * @param $type
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public abstract function listResources($type, $limit, $offset);

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
    public abstract function get($id, $type);

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
