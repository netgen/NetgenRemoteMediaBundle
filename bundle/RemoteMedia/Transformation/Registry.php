<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;

class Registry
{
    /**
     * @var \Netgen\Bundle\OpenGraphBundle\Handler\HandlerInterface[]
     */
    protected $transformationHandlers = [];

    /**
     * Adds a handler to the registry.
     *
     * @param string $provider
     * @param string $identifier
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface $transformationHandler
     */
    public function addHandler($provider, $identifier, HandlerInterface $transformationHandler)
    {
        if (!isset($this->transformationHandlers[$provider][$identifier])) {
            $this->transformationHandlers[$provider][$identifier] = $transformationHandler;
        }
    }

    /**
     * Returns handler by its identifier.
     *
     * @param string $identifier
     * @param string $provider
     *
     * @throws \Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException If the handler is not found
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface
     */
    public function getHandler($identifier, $provider)
    {
        if (isset($this->transformationHandlers[$provider][$identifier])) {
            return $this->transformationHandlers[$provider][$identifier];
        }

        throw new TransformationHandlerNotFoundException($provider, $identifier);
    }
}
