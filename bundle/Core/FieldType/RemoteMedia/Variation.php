<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value;
use function json_encode;

class Variation extends Value
{
    public $url;
    public $width;
    public $height;
    public $coords = ['x' => 0, 'y' => 0];

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
