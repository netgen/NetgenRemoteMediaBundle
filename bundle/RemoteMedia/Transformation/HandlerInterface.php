<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

interface HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @param string $variationName name of the configured image variation configuration
     *
     * @return array
     */
    public function process(Value $value, $variationName, array $config = []);
}
