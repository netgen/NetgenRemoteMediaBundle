<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class MimeTypeNotFoundException extends Exception
{
    public function __construct(string $uri, string $type)
    {
        parent::__construct(sprintf('Mime type was not found for path "%s" of type "%s".', $uri, $type));
    }
}
