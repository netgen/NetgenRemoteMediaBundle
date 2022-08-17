<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

final class RemoteResourceVariation
{
    private RemoteResource $resource;
    private string $url;

    public function __construct(RemoteResource $resource, string $url)
    {
        $this->resource = $resource;
        $this->url = $url;
    }

    public static function fromResource(RemoteResource $resource): self
    {
        return new self(
            $resource,
            $resource->getUrl(),
        );
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->resource;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
