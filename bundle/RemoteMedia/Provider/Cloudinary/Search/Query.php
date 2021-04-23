<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search;

final class Query
{
    /** @var string */
    private $query;

    /** @var string */
    private $resourceType;

    /** @var string|null */
    private $folder;

    /** @var string|null */
    private $tag;

    /** @var int */
    private $limit;

    /** @var string|null */
    private $nextCursor;

    /** @var array */
    private $sortBy = ['created_at' => 'desc'];

    /**
     * Query constructor.
     */
    public function __construct(
        string $query,
        ?string $resourceType,
        int $limit,
        ?string $folder = null,
        ?string $tag = null,
        ?string $nextCursor = null,
        array $sortBy = ['created_at' => 'desc']
    ) {
        $this->query = $query;
        $this->resourceType = $resourceType;
        $this->folder = $folder;
        $this->tag = $tag;
        $this->limit = $limit;
        $this->nextCursor = $nextCursor;
        $this->sortBy = $sortBy;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getResourceType(): ?string
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

    public function getSortBy(): array
    {
        return $this->sortBy;
    }

    public function __toString()
    {
        $vars = \get_object_vars($this);
        $sort = \http_build_query($vars['sortBy'], '', ',');
        $folder = $vars['folder'] === '' ? '(root)' : $vars['folder'];
        unset($vars['sortBy']);
        unset($vars['folder']);

        return \implode('|', $vars) . $folder . '|' . $sort;
    }
}
