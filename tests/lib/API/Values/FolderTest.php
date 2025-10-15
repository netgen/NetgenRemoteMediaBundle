<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Values;

use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Folder::class)]
final class FolderTest extends TestCase
{
    #[DataProvider('dataProvider')]
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

    #[DataProvider('dataProvider')]
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
