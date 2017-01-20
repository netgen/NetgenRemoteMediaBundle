<?php

namespace Netgen\Bundle\RemoteMediaBundle\Exception;

use Exception;

class TransformationHandlerNotFoundException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $handlerIdentifier
     */
    public function __construct($provider, $handlerIdentifier)
    {
        parent::__construct("[NgRemoteMedia] Transformation handler with '$handlerIdentifier' identifier for '$provider' provider not found.");
    }
}
