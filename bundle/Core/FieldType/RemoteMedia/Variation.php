<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Variation extends BaseValue
{
    public $url = null;
    public $width = null;
    public $height = null;
    public $coords = array('x' => 0, 'y' => 0);

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
