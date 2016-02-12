<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface RemoteMediaProviderInterface
{
    /**
     * Returns the array with options required for the upload.
     *
     * @param string $id
     * @param string $resourceType
     * @param string $altText
     * @param string $caption
     *
     * @return array
     */
    public function prepareUploadOptions($id, $resourceType = null, $altText = '', $caption = '');

    /**
     * Uploads the local resource to remote storage.
     *
     * @param string $fileUri
     * @param array $options
     *
     * @return mixed
     */
    public function upload($fileUri, $options = array());

    /**
     * Gets the absolute url of the remote resource formatted according to options provided.
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    public function getFormattedUrl($source, $options = array());

    /**
     * Transforms response from the remote storage to field type value.
     *
     * @param mixed $response
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function getValueFromResponse($response);

    /**
     * Gets the remote media Variation.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $format
     * @param array $namedFormats
     * @param bool $secure
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getVariation(Value $value, $format, array $namedFormats, $secure = true);

    /**
     * Lists all available resources from the remote storage.
     *
     * @param int $limit
     *
     * @return array
     */
    public function listResources($limit = 10);

    /**
     * Counts available resources from the remote storage.
     *
     * @return int
     */
    public function countResources();

    /**
     * Searches for the remote resource containing term in the query.
     *
     * @param string $query
     * @param string $resourceType
     * @param int $limit
     *
     * @return array
     */
    public function searchResources($query, $limit = 10);

    /**
     * Searches for the remote resource tagged with a provided tag.
     *
     * @param string $tag
     *
     * @return array
     */
    public function searchResourcesByTag($tag);

    /**
     * Returns the remote resource with provided id and type.
     *
     * @param mixed $resourceId
     * @param string $resourceType
     *
     * @return array
     */
    public function getRemoteResource($resourceId, $resourceType);

    /**
     * Adds tag to remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    public function addTagToResource($resourceId, $tag);

    /**
     * Removes tag from remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    public function removeTagFromResource($resourceId, $tag);

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = array(
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * );
     *
     * @param mixed $resourceId
     * @param string $resourceType
     * @param array $context
     *
     * @return mixed
     */
    public function updateResourceContext($resourceId, $resourceType, $context);

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param mixed $resourceId
     * @param mixed|null $offset
     *
     * @return string
     */
    public function getVideoThumbnail($resourceId, $offset = null);

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param mixed $resourceId
     * @param string $format
     * @param array $namedFormats
     *
     * @return string
     */
    public function generateVideoTag($resourceId, $format = '', $namedFormats = array());

    /**
     * Formats browse list to comply with eZExceed.
     * If eZExceed is not used, this method can be left blank.
     *
     * @param array $list
     *
     * @return array
     */
    public function formatBrowseList(array $list);

    public function deleteResource($resourceId);
}
