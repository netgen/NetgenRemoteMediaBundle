<?php

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Converter;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\Core\MVC\Legacy\Templating\Converter\ObjectConverter;

class RemoteMediaValueConverter implements ObjectConverter
{
    /**
     * Converts $object to make it compatible with eZTemplate API.
     * This implementation returns the value "as is".
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $object
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function convert($object)
    {
        return $object;
    }
}
