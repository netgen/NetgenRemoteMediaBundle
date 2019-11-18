<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value;

class Variation extends Value
{
    public $url = null;
    public $width = null;
    public $height = null;
    public $coords = ['x' => 0, 'y' => 0];

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return \json_encode($this);
    }
}
