<?php

namespace Netgen\Bundle\RemoteMediaBundle\Exception;

use Exception;

class TransformationHandlerFailedException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $handlerIdentifier
     * @param mixed $handlerClass
     */
    public function __construct($handlerClass)
    {
        parent::__construct("Transformation handler '$handlerClass' identifier failed.");
    }
}
