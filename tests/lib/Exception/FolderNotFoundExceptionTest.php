<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Exception\FolderNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FolderNotFoundException::class)]
final class FolderNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(FolderNotFoundException::class);
        $this->expectExceptionMessage('Folder with path "media/images/new" was not found on remote.');

        throw new FolderNotFoundException(Folder::fromPath('media/images/new'));
    }
}
