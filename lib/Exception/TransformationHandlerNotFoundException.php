<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use function sprintf;

final class TransformationHandlerNotFoundException extends Exception
{
    public function __construct(string $provider, string $handlerIdentifier)
    {
        parent::__construct(sprintf('Transformation handler with "%s" identifier for "%s" provider not found.', $handlerIdentifier, $provider));
    }
}
