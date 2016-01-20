<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

interface RemoteMediaProviderInterface
{
    public function upload($fileUri, $options = array());

    public function getFormattedUrl($source, $options = array());

    public function getValueFromResponse($response);
}
