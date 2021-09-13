<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Transformation;

use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;

final class Registry
{
    /** @var \Netgen\RemoteMedia\Core\Transformation\HandlerInterface[] */
    private array $transformationHandlers = [];

    public function addHandler(string $provider, string $identifier, HandlerInterface $transformationHandler): void
    {
        if (!isset($this->transformationHandlers[$provider][$identifier])) {
            $this->transformationHandlers[$provider][$identifier] = $transformationHandler;
        }
    }

    public function getHandler(string $identifier, string $provider): HandlerInterface
    {
        if (isset($this->transformationHandlers[$provider][$identifier])) {
            return $this->transformationHandlers[$provider][$identifier];
        }

        throw new TransformationHandlerNotFoundException($provider, $identifier);
    }
}
