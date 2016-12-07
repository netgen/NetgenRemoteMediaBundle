<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;

class Registry
{
    /**
     * @var \Netgen\Bundle\OpenGraphBundle\Handler\HandlerInterface[]
     */
    protected $transformationHandlers = array();

    /**
     * Adds a handler to the registry.
     *
     * @param string $provider
     * @param string $identifier
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface $transformationHandler
     */
    public function addHandler($provider, $identifier, TransformationInterface $transformationHandler)
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
     * @return \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface
     */
    public function getHandler($identifier, $provider)
    {
        if (isset($this->transformationHandlers[$provider][$identifier])) {
            return $this->transformationHandlers[$provider][$identifier];
        }

        throw new TransformationHandlerNotFoundException($provider, $identifier);
    }
}
