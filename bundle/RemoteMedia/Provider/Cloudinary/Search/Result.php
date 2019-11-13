<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search;

use Cloudinary\Api\Response;

final class Result
{
    /** @var int */
    private $totalCount;

    /** @var string */
    private $nextCursor;

    /** @var array */
    private $results;

    private function __construct(int $totalCount, ?string $nextCursor, array $results)
    {
        $this->totalCount = $totalCount;
        $this->nextCursor = $nextCursor;
        $this->results = $results;
    }

    public static function fromResponse(Response $response): self
    {
        $totalCount = $response['total_count'];
        $nextCursor = $response['next_cursor'];
        $results = $response['resources'];

        return new Result($totalCount, $nextCursor, $results);
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
