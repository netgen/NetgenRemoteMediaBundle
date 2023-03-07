<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

final class Result
{
    private int $totalCount;

    private ?string $nextCursor;

    /** @var \Netgen\RemoteMedia\API\Values\RemoteResource[] */
    private array $resources = [];

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
