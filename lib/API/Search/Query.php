<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

use function get_object_vars;
use function http_build_query;
use function implode;
use function is_array;
use function property_exists;

final class Query
{
    private ?string $query = null;

    /** @var string[] */
    private array $types = [];

    /** @var string[] */
    private array $folders = [];

    /** @var string[] */
    private array $visibilities = [];

    /** @var string[] */
    private array $tags = [];

    /** @var string[] */
    private array $remoteIds = [];

    /** @var array<string,string|string[]> */
    private array $context = [];

    private int $limit = 25;

    private ?string $nextCursor = null;

    /** @var array<string, string> */
    private array $sortBy = ['created_at' => 'desc'];

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function __toString(): string
    {
        $vars = get_object_vars($this);
        $types = implode(',', $this->types);
        $folders = implode(',', $this->folders);
        $visibilities = implode(',', $this->visibilities);
        $tags = implode(',', $this->tags);
        $remoteIds = implode(',', $this->remoteIds);
        $sort = http_build_query($vars['sortBy'], '', ',');

        $context = [];
        foreach ($this->context as $key => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $val) {
                $context[] = ($key . '=' . $val);
            }
        }

        $context = implode(',', $context);

        unset(
            $vars['types'],
            $vars['folders'],
            $vars['visibilities'],
            $vars['tags'],
            $vars['remoteIds'],
            $vars['context'],
            $vars['sortBy'],
        );

        return implode('|', $vars) . '|' . $types . '|' . $folders . '|' . $visibilities . '|' . $tags . '|' . $remoteIds . '|' . $context . '|' . $sort;
    }

    /**
     * @param string[] $remoteIds
     * @param array<string, string> $sortBy
     */
    public static function fromRemoteIds(
        array $remoteIds,
        int $limit = 25,
        ?string $nextCursor = null,
        array $sortBy = ['created_at' => 'desc']
    ): self {
        return new self([
            'remoteIds' => $remoteIds,
            'limit' => $limit,
            'nextCursor' => $nextCursor,
            'sortBy' => $sortBy,
        ]);
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return string[]
     */
    public function getFolders(): array
    {
        return $this->folders;
    }

    /**
     * @return string[]
     */
    public function getVisibilities(): array
    {
        return $this->visibilities;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string[]
     */
    public function getRemoteIds(): array
    {
        return $this->remoteIds;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getContext(): array
    {
        return $this->context;
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
