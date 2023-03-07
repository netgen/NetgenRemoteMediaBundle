<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Transformation;

interface HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @throws \Netgen\RemoteMedia\Exception\TransformationHandlerFailedException
     */
    public function process(array $config = []): array;
}
