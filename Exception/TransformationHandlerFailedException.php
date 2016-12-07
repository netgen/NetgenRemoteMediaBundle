<?php

namespace Netgen\Bundle\RemoteMediaBundle\Exception;

use Exception;

class TransformationHandlerFailedException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $handlerIdentifier
     */
    public function __construct($handlerClass)
    {
        parent::__construct("Transformation handler '$handlerClass' identifier failed.");
    }
}
