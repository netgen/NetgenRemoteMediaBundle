<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

class AuthenticatedRemoteResource extends RemoteResource
{
    public function __construct(
        RemoteResource $remoteResource,
        string $url,
        private AuthToken $token,
    ) {
        parent::__construct(
            remoteId: $remoteResource->getRemoteId(),
            type: $remoteResource->getType(),
            url: $url,
            md5: $remoteResource->getMd5(),
            id: $remoteResource->getId(),
            name: $remoteResource->getName(),
            originalFilename: $remoteResource->getOriginalFilename(),
            version: $remoteResource->getVersion(),
            visibility: $remoteResource->getVisibility(),
            folder: $remoteResource->getFolder(),
            size: $remoteResource->getSize(),
            altText: $remoteResource->getAltText(),
            caption: $remoteResource->getCaption(),
            tags: $remoteResource->getTags(),
            metadata: $remoteResource->getMetadata(),
            context: $remoteResource->getContext(),
            locations: $remoteResource->getLocations(),
        );
    }

    public function getToken(): AuthToken
    {
        return $this->token;
    }

    public function isValid(?string $ipAddress = null): bool
    {
        return $this->token->isValid($ipAddress);
    }
}
