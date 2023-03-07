<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class NotSupportedException extends Exception
{
    public function __construct(string $providerIdentifier, string $what)
    {
        parent::__construct(sprintf('Provider "%s" does not support "%s".', $providerIdentifier, $what));
    }
}
