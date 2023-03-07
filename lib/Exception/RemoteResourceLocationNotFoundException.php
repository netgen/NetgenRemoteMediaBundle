<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class RemoteResourceLocationNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Remote resource location with ID "%s" not found.', $id));
    }
}
