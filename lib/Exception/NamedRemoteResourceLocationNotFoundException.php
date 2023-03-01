<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class NamedRemoteResourceLocationNotFoundException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Named remote resource location with name "%s" not found.', $name));
    }
}
