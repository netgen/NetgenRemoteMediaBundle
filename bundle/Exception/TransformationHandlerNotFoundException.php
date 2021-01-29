<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Exception;

use Exception;

class TransformationHandlerNotFoundException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $handlerIdentifier
     * @param mixed $provider
     */
    public function __construct($provider, $handlerIdentifier)
    {
        parent::__construct(sprintf('[NgRemoteMedia] Transformation handler with \'%s\' identifier for \'%s\' provider not found.', $handlerIdentifier, $provider));
    }
}
