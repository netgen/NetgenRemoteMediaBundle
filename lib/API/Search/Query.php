<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

use function get_object_vars;
use function http_build_query;
use function implode;
use function is_array;

final class Query
{
    /** @var string */
    private string $query;

    /** @var string|string[]|null */
    private $resourceType;

    private ?string $folder;

    private ?string $tag;

    /** @var string[] */
    private array $resourceIds;

    private int $limit;

    private ?string $nextCursor;

    /** @var array<string, string> */
    private array $sortBy;

    /**
     * @param string|string[]|null $resourceType
     * @param array<string, string> $sortBy
     * @param string[] $resourceIds
     */
    public function __construct(
        string $query,
        $resourceType,
        int $limit,
        ?string $folder = null,
        ?string $tag = null,
        ?string $nextCursor = null,
        array $sortBy = ['created_at' => 'desc'],
        array $resourceIds = []
    ) {
        $this->query = $query;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->tag = $tag;
        $this->limit = $limit;
        $this->nextCursor = $nextCursor;
        $this->sortBy = $sortBy;
        $this->resourceIds = $resourceIds;
    }

    public function __toString(): string
    {
        $vars = get_object_vars($this);
        $sort = http_build_query($vars['sortBy'], '', ',');
        $folder = $vars['folder'] === '' ? '(root)' : $vars['folder'];
        $resourceIds = implode(',', $this->resourceIds);

        if (is_array($vars['resourceType'])) {
            $vars['resourceType'] = implode(',', $vars['resourceType']);
        }

        unset($vars['sortBy'], $vars['folder'], $vars['resourceIds']);

        return implode('|', $vars) . $folder . '|' . $sort . '|' . $resourceIds;
    }

    /**
     * @param string[] $resourceIds
     * @param array<string, string> $sortBy
     */
    public static function createResourceIdsSearchQuery(
        array $resourceIds,
        int $limit = 500,
        ?string $nextCursor = null,
        array $sortBy = ['created_at' => 'desc']
    ) {
        return new self(
            '',
            null,
            $limit,
            null,
            null,
            $nextCursor,
            $sortBy,
            $resourceIds,
        );
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string|string[]|null
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @return string[]
     */
    public function getResourceIds(): array
    {
        return $this->resourceIds;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function setNextCursor(?string $nextCursor): void
    {
        $this->nextCursor = $nextCursor;
    }

    /**
     * @return array<string, string>
     */
    public function getSortBy(): array
    {
        return $this->sortBy;
    }
}
