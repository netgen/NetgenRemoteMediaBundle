<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Utils;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

final class CircularReferenceHandler
{
    public function __invoke($object)
    {
        if ($object instanceof RemoteResourceLocation || $object instanceof RemoteResource) {
            return $object->getId();
        }

        return $object;
    }
}
