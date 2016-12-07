<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface TransformationInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $alias
     * @param array $config
     *
     * @return array
     */
    public function process(Value $value, $alias, array $config = array());
}
