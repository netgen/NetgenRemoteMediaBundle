<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;

use function sprintf;

final class MimeCategoryParseException extends Exception
{
    public function __construct(string $mimeType)
    {
        parent::__construct(sprintf('Could not parse mime category for given mime type: %s.', $mimeType));
    }
}
