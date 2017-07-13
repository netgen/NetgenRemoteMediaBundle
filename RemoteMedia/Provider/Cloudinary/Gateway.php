<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

abstract class Gateway
{
    public abstract function upload($fileUri, $options);

    public abstract function getVariationUrl($source, $options);

    public abstract function search($query, $options = array(), $limit = 10);

    public abstract function listResources($options);

    public abstract function countResources();

    public abstract function countResourcesInFolder($folder);

    public abstract function get($id, $options);

    public abstract function addTag($id, $tag);

    public abstract function removeTag($id, $tag);

    public abstract function update($id, $options);

    public abstract function getVideoThumbnail($id, $options = array());

    public abstract function getVideoTag($id, $options = array());

    public abstract function getDownloadLink($id, $options);

    public abstract function delete($id);

    public abstract function listFolders();
}
