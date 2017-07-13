<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\ContentBrowser;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\CloudinaryProvider;

class AdminProvider extends CloudinaryProvider
{
    public function listFolders()
    {
        $list = $this->gateway->listFolders();

        return $list;
    }

    public function countResourcesInFolder($folder)
    {
        return $this->gateway->countResourcesInFolder($folder);
    }
}
