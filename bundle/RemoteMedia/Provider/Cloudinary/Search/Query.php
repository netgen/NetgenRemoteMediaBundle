<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search;

use function get_object_vars;
use function http_build_query;
use function implode;

final class Query
{
    /** @var string */
    private $query;

    /** @var string|string[]|null */
    private $resourceType;

    /** @var string|null */
    private $folder;

    /** @var string|null */
    private $tag;

    /** @var string[] */
    private $resourceIds;

    /** @var int */
    private $limit;

    /** @var string|null */
    private $nextCursor;

    /** @var array */
    private $sortBy = ['created_at' => 'desc'];

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

    public function __toString()
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

    /**
     * @return string
     */
    public function getFolder(): ?string
    {
        return $this->folder;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function setNextCursor(?string $nextCursor): void
    {
        $this->nextCursor = $nextCursor;
    }

    public function getSortBy(): array
    {
        return $this->sortBy;
    }
}
