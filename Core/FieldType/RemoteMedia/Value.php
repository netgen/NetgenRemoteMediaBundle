<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public $public_id = null;
    public $version = null;
    public $width = null;
    public $height = null;
    public $format = null;
    public $url = null;
    public $secure_url = null;
    public $input_uri = null;
    public $resource_type = null;
    public $created_at = null;
    public $tags = array();
    public $original_filename = null;
    public $signature = null;
    public $bytes = null;
    public $type = null;
    public $etag = null;
    public $overwritten = null;

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
