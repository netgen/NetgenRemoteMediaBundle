<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value;
use function json_encode;

class InputValue extends Value
{
    public $input_uri;
    public $alt_text = '';
    public $caption = '';
    public $variations = [];

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
