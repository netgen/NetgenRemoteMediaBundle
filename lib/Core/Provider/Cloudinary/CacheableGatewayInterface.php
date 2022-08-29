<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

/**
 * @internal
 */
interface CacheableGatewayInterface extends GatewayInterface
{
    public function invalidateResourceListCache(): void;

    public function invalidateResourceCache(CloudinaryRemoteId $remoteId): void;

    public function invalidateFoldersCache(): void;

    public function invalidateTagsCache(): void;
}
