<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

interface RemoteMediaInterface
{
    public function upload($fileUri, $options);
}
