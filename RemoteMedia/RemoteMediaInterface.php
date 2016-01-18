<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

interface RemoteMediaInterface
{
    public function upload(VersionInfo $versionInfo, Field $field);
}
