<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

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
    public abstract function upload($fileUri, $options);

    /**
     * Generates url to the media with provided options
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    public abstract function getVariationUrl($source, $options);

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
    public abstract function search($query, $options = array(), $limit = 10, $offset = 0);

    /**
     * List all available resources.
     *
     * @param $options
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    public abstract function listResources($options, $offset, $limit);

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public abstract function listFolders();

    /**
     * Returns the overall resources usage on the cloudinary account.
     *
     * @return int
     */
    public abstract function countResources();

    /**
     * Returns the number of resources in the provided folder.
     *
     * @param $folder
     *
     * @return int
     */
    public abstract function countResourcesInFolder($folder);

    /**
     * Fetches the remote resource by id.
     *
     * @param $id
     * @param $options
     *
     * @return array
     */
    public abstract function get($id, $options);

    /**
     * Adds new tag to the remote resource.
     *
     * @param $id
     * @param $tag
     *
     * @return array
     */
    public abstract function addTag($id, $tag);

    /**
     * Removes the tag from the remote resource.
     *
     * @param $id
     * @param $tag
     *
     * @return array
     */
    public abstract function removeTag($id, $tag);

    /**
     * Updates the remote resource.
     *
     * @param $id
     * @param $options
     */
    public abstract function update($id, $options);

    /**
     * Returns the url for the thumbnail of video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    public abstract function getVideoThumbnail($id, $options = array());

    /**
     * Generates video tag for the video with the provided id.
     *
     * @param $id
     * @param array $options
     *
     * @return string
     */
    public abstract function getVideoTag($id, $options = array());

    /**
     * Generates download link for the remote resource.
     *
     * @param $id
     * @param $options
     *
     * @return string
     */
    public abstract function getDownloadLink($id, $options);

    /**
     * Deletes the resource from the cloudinary.
     *
     * @param $id
     */
    public abstract function delete($id);
}
