<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception\Cloudinary;

use Exception;

use function sprintf;

final class InvalidRemoteIdException extends Exception
{
    public function __construct(string $remoteId)
    {
        parent::__construct(sprintf('Provided remoteId "%s" is invalid.', $remoteId));
    }
}
