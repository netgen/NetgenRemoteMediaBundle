<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class InputValue extends BaseValue
{
    public $input_uri = null;
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
        return \json_encode($this);
    }
}
