<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function sprintf;

final class RemoteResourceExistsException extends Exception
{
    private RemoteResource $remoteResource;

    public function __construct(RemoteResource $remoteResource)
    {
        $this->remoteResource = $remoteResource;

        parent::__construct(sprintf('Remote resource with ID "%s" already exists.', $remoteResource->getRemoteId()));
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->remoteResource;
    }
}
