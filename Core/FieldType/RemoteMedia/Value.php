<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_OTHER = 'other';

    public $resourceId = null;
    public $url = null;
    public $secure_url = null;
    public $size = null;

    public $mediaType = 'image';

    public $variations = array();
    public $metaData = array(
        'format' => '',
        'alt_text' => '',
        'caption' => '',
        'width' => '',
        'height' => '',
        'tags' => array(),
    );

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
