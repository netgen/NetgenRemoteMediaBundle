<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface RemoteMediaProviderInterface
{
    public function upload($fileUri, $options = array());

    public function getFormattedUrl($source, $options = array());

    public function getValueFromResponse($response);

    public function getVariation(Value $value, array $namedFormats, $format, $secure = true);
}
