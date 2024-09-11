<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Transformation;

use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

interface HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @throws TransformationHandlerFailedException
     */
    public function process(array $config = []): array;
}
