<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\TestCase;

final class FolderTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Values\Folder::__construct
     * @covers \Netgen\RemoteMedia\API\Values\Folder::__toString
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getName
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getParent
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getPath
     * @covers \Netgen\RemoteMedia\API\Values\Folder::isRoot
     *
     * @dataProvider dataProvider
     */
    public function testCreate(string $name, ?Folder $parent, bool $isRoot, string $path): void
    {
        $folder = new Folder($name, $parent);

        self::assertSame(
            $name,
            $folder->getName(),
        );

        if ($parent instanceof Folder) {
            self::assertInstanceOf(
                Folder::class,
                $folder->getParent(),
            );

            self::assertSame(
                $parent->getPath(),
                $folder->getParent()->getPath(),
            );
        }

        if (!$parent instanceof Folder) {
            self::assertNull($folder->getParent());
        }

        self::assertSame(
            $isRoot,
            $folder->isRoot(),
        );

        self::assertSame(
            $path,
            $folder->getPath(),
        );

        self::assertSame(
            $path,
            (string) $folder,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\API\Values\Folder::__construct
     * @covers \Netgen\RemoteMedia\API\Values\Folder::fromPath
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getName
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getParent
     * @covers \Netgen\RemoteMedia\API\Values\Folder::getPath
     * @covers \Netgen\RemoteMedia\API\Values\Folder::isRoot
     *
     * @dataProvider dataProvider
     */
    public function testCreateFromPath(string $name, ?Folder $parent, bool $isRoot, string $path): void
    {
        $folder = Folder::fromPath($path);

        self::assertSame(
            $name,
            $folder->getName(),
        );

        if ($parent instanceof Folder) {
            self::assertInstanceOf(
                Folder::class,
                $folder->getParent(),
            );

            self::assertSame(
                $parent->getPath(),
                $folder->getParent()->getPath(),
            );
        }

        if (!$parent instanceof Folder) {
            self::assertNull($folder->getParent());
        }

        self::assertSame(
            $isRoot,
            $folder->isRoot(),
        );

        self::assertSame(
            $path,
            $folder->getPath(),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                'media',
                null,
                true,
                'media',
            ],
            [
                'images',
                Folder::fromPath('media'),
                false,
                'media/images',
            ],
            [
                'articles & news',
                Folder::fromPath('media/images/content'),
                false,
                'media/images/content/articles & news',
            ],
        ];
    }
}
