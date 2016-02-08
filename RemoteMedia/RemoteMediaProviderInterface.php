<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface RemoteMediaProviderInterface
{
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
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param array $namedFormats
     * @param string $format
     * @param bool $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getVariation(Value $value, array $namedFormats, $format, $secure = true);

    /**
     * Lists all available resources from the remote storage.
     *
     * @return array
     */
    public function listResources();

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
     *
     * @return array
     */
    public function searchResources($query, $resourceType);

    public function addTagToResource($resourceId, $tag);

    public function removeTagFromResource($resourceId, $tag);

    /**
     * context = array(
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * )
     *
     * @param $resourceId
     * @param $resourceType
     * @param $context
     *
     * @return mixed
     */
    public function updateResourceContext($resourceId, $resourceType, $context);
}
