<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Transformation;

use Netgen\RemoteMedia\API\Values\RemoteResource;

interface HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array;
}
