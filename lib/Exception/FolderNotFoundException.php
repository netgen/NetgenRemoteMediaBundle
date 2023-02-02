<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use Netgen\RemoteMedia\API\Values\Folder;

use function sprintf;

final class FolderNotFoundException extends Exception
{
    public function __construct(Folder $folder)
    {
        parent::__construct(sprintf('Folder with path "%s" was not found on remote.', $folder->getPath()));
    }
}
