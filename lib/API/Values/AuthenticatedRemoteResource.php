<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

class AuthenticatedRemoteResource
{
    public function __construct(
        private RemoteResource $remoteResource,
        private string $url,
        private AuthToken $token
    ) {
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->remoteResource;
    }

    public function getUrl(): string
    {
        return $this->url;
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
