<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Search;

use Cloudinary\Api\Response;

final class Result
{
    private int $totalCount;

    private ?string $nextCursor;

    /** @var \Netgen\RemoteMedia\API\Values\RemoteResource[] */
    private array $results = [];

    private function __construct(int $totalCount, ?string $nextCursor, array $results)
    {
        $this->totalCount = $totalCount;
        $this->nextCursor = $nextCursor;
        $this->results = $results;
    }

    public static function fromResponse(Response $response): self
    {
        $totalCount = $response['total_count'];
        $nextCursor = $response['next_cursor'] ?? null;
        $results = $response['resources'];

        return new self($totalCount, $nextCursor, $results);
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
    public function getResults(): array
    {
        return $this->results;
    }
}
