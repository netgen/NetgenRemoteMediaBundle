<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use function sprintf;

final class RemoteResourceNotFoundException extends Exception
{
    public function __construct(string $resourceId)
    {
        parent::__construct(sprintf('Remote resource with ID "%s" not found.', $resourceId));
    }
}
