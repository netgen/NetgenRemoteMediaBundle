<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use function sprintf;

final class RemoteResourceNotFoundException extends Exception
{
    public function __construct(string $resourceId, string $resourceType)
    {
        parent::__construct(sprintf('[NgRemoteMedia] Remote resource with ID \'%s\' of \'%s\' type not found.', $resourceId, $resourceType));
    }
}
