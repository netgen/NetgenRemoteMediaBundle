<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

use Netgen\RemoteMedia\API\Values\RemoteResource;

final class Result
{
    /**
     * @param RemoteResource[] $resources
     */
    public function __construct(
        private int $totalCount,
        private ?string $nextCursor = null,
        private array $resources = []
    ) {}

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * @return RemoteResource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
