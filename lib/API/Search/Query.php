<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Search;

use function get_object_vars;
use function http_build_query;
use function implode;
use function is_array;

final class Query
{
    /**
     * @param string[] $types
     * @param \Netgen\RemoteMedia\API\Values\Folder[] $folders
     * @param string[] $visibilities
     * @param string[] $tags
     * @param string[] $remoteIds
     * @param array<string,string|string[]> $context
     * @param array<string,string> $sortBy
     */
    public function __construct(
        private ?string $query = null,
        private array $types = [],
        private array $folders = [],
        private array $visibilities = [],
        private array $tags = [],
        private array $remoteIds = [],
        private array $context = [],
        private int $limit = 25,
        private ?string $nextCursor = null,
        private array $sortBy = ['created_at' => 'desc']
    ) {
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
        return new self(
            remoteIds: $remoteIds,
            limit: $limit,
            nextCursor: $nextCursor,
            sortBy: $sortBy,
        );
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
