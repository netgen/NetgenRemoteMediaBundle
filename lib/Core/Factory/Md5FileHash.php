<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;

use function md5_file;

final class Md5FileHash implements FileHashFactoryInterface
{
    public function createHash(string $path): string
    {
        return md5_file($path);
    }
}
