<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface RemoteMediaProviderInterface
{
    /**
     * @param $fileUri
     * @param array $options
     *
     * @return mixed
     */
    public function upload($fileUri, $options = array());

    /**
     * @param $source
     * @param array $options
     *
     * @return string
     */
    public function getFormattedUrl($source, $options = array());

    /**
     * @param $response
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function getValueFromResponse($response);

    /**
     * @param Value $value
     * @param array $namedFormats
     * @param $format
     * @param bool|true $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getVariation(Value $value, array $namedFormats, $format, $secure = true);

    /**
     * @return array
     */
    public function listResources();

    /**
     * @return int
     */
    public function countResources();

    /**
     * @param $query
     * @param $resourceType
     *
     * @return array
     */
    public function searchResources($query, $resourceType);
}
