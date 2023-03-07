<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class NamedRemoteResourceNotFoundException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Named remote resource with name "%s" not found.', $name));
    }
}
