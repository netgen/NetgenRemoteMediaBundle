<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

interface FileHash
{
    public function createHash(string $path): string;
}
