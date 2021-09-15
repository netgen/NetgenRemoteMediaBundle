<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

use Cloudinary\Api\Response;
use Netgen\RemoteMedia\API\Values\RemoteResource;

final class Result
{
    private int $totalCount;

    private ?string $nextCursor;

    /** @var \Netgen\RemoteMedia\API\Values\RemoteResource[] */
    private array $resources = [];

    private function __construct(int $totalCount, ?string $nextCursor, array $resources)
    {
        $this->totalCount = $totalCount;
        $this->nextCursor = $nextCursor;
        $this->resources = $resources;
    }

    public static function fromResponse(Response $response): self
    {
        $totalCount = $response['total_count'];
        $nextCursor = $response['next_cursor'] ?? null;

        $resources = [];
        foreach ($response['resources'] as $resourceData) {
            $resources[] = RemoteResource::createFromCloudinaryResponse($resourceData);
        }

        return new self($totalCount, $nextCursor, $resources);
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
