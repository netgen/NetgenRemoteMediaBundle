<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

abstract class Gateway
{
    abstract public function upload($fileUri, $options);

    abstract public function getVariationUrl($source, $options);

    abstract public function search($query, $options = array(), $limit = 10);

    abstract public function listResources($options);

    abstract public function countResources();

    abstract public function get($id, $options);

    abstract public function addTag($id, $tag);

    abstract public function removeTag($id, $tag);

    abstract public function update($id, $options);

    abstract public function getVideoThumbnail($id, $options = array());

    abstract public function getVideoTag($id, $options = array());

    abstract public function getDownloadLink($id, $options);

    abstract public function delete($id);
}
