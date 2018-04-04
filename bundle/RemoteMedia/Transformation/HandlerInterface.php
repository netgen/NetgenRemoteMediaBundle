<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $variationName name of the configured image variation configuration
     * @param array $config
     *
     * @return array
     */
    public function process(Value $value, $variationName, array $config = []);
}
