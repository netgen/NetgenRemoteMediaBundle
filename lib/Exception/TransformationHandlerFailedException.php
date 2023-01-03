<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class TransformationHandlerFailedException extends Exception
{
    public function __construct(string $handlerClass)
    {
        parent::__construct(sprintf('Transformation handler "%s" identifier failed.', $handlerClass));
    }
}
