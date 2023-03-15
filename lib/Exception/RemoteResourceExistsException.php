<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function sprintf;

final class RemoteResourceExistsException extends Exception
{
    public function __construct(private RemoteResource $remoteResource)
    {
        parent::__construct(sprintf('Remote resource with ID "%s" already exists.', $remoteResource->getRemoteId()));
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->remoteResource;
    }
}
