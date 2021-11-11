<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use Cloudinary\Api\Response;
use Netgen\RemoteMedia\API\Values\RemoteResource;

final class SearchResult
{
    private int $totalCount;

    private ?string $nextCursor;

    /** @var \Netgen\RemoteMedia\API\Values\RemoteResource[] */
    private array $resources = [];

    /**
     * @param \Netgen\RemoteMedia\API\Values\RemoteResource[] $resources
     */
    public function __construct(int $totalCount, ?string $nextCursor, array $resources)
    {
        $this->totalCount = $totalCount;
        $this->nextCursor = $nextCursor;
        $this->resources = $resources;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\RemoteResource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
