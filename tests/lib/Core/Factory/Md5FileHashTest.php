<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\Core\Factory\Md5FileHash as Md5FileHashFactory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function md5_file;

#[CoversClass(FileHashFactoryInterface::class)]
#[CoversClass(Md5FileHashFactory::class)]
final class Md5FileHashTest extends TestCase
{
    private FileHashFactoryInterface $md5FileHashFactory;

    protected function setUp(): void
    {
        $this->md5FileHashFactory = new Md5FileHashFactory();
    }

    public function testCreateCurrent(): void
    {
        $media = vfsStream::setup('media');
        $file = vfsStream::newFile('test.txt')->at($media);

        self::assertGreaterThanOrEqual(
            md5_file($file->url()),
            $this->md5FileHashFactory->createHash($file->url()),
        );
    }
}
