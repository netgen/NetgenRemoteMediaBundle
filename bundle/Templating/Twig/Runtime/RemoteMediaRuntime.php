<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    protected RemoteMediaProvider $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getRemoteResource(string $resourceId, string $resourceType): ?RemoteResource
    {
        try {
            return $this->provider->getRemoteResource($resourceId, $resourceType);
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function getDownloadUrl(RemoteResource $resource): string
    {
        return $this->provider->generateDownloadLink($resource);
    }

    public function getVideoThumbnailUrl(RemoteResource $resource): string
    {
        return $this->provider->getVideoThumbnail($resource);
    }
}
