<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Query;

interface NextCursorResolverInterface
{
    public function resolve(Query $query, int $offset): string;

    public function save(Query $query, int $offset, string $cursor): void;
}
