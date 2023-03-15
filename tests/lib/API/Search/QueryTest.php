<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Search;

use Netgen\RemoteMedia\API\Search\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
final class QueryTest extends TestCase
{
    #[DataProvider('constructorPropsProvider')]
    public function testConstructor(
        ?string $searchQuery,
        array $types,
        array $folders,
        array $visibilities,
        array $tags,
        array $remoteIds,
        array $context,
        int $limit,
        ?string $nextCursor,
        array $sortBy,
        string $toString
    ): void {
        $query = new Query(
            query: $searchQuery,
            types: $types,
            folders: $folders,
            visibilities: $visibilities,
            tags: $tags,
            remoteIds: $remoteIds,
            context: $context,
            limit: $limit,
            nextCursor: $nextCursor,
            sortBy: $sortBy,
        );

        self::assertSame(
            $searchQuery,
            $query->getQuery(),
        );

        self::assertSame(
            $types,
            $query->getTypes(),
        );

        self::assertSame(
            $folders,
            $query->getFolders(),
        );

        self::assertSame(
            $visibilities,
            $query->getVisibilities(),
        );

        self::assertSame(
            $tags,
            $query->getTags(),
        );

        self::assertSame(
            $remoteIds,
            $query->getRemoteIds(),
        );

        self::assertSame(
            $context,
            $query->getContext(),
        );

        self::assertSame(
            $limit,
            $query->getLimit(),
        );

        self::assertSame(
            $nextCursor,
            $query->getNextCursor(),
        );

        self::assertSame(
            $sortBy,
            $query->getSortBy(),
        );

        self::assertSame(
            $toString,
            (string) $query,
        );
    }

    #[DataProvider('remoteIdsProvider')]
    public function testFromRemoteIds(
        array $remoteIds,
        int $limit,
        ?string $nextCursor,
        array $sortBy,
        string $toString
    ): void {
        $query = Query::fromRemoteIds(
            $remoteIds,
            $limit,
            $nextCursor,
            $sortBy,
        );

        self::assertNull($query->getQuery());
        self::assertEmpty($query->getTypes());
        self::assertEmpty($query->getFolders());
        self::assertEmpty($query->getVisibilities());
        self::assertEmpty($query->getTags());
        self::assertEmpty($query->getContext());

        self::assertSame(
            $remoteIds,
            $query->getRemoteIds(),
        );

        self::assertSame(
            $limit,
            $query->getLimit(),
        );

        self::assertSame(
            $nextCursor,
            $query->getNextCursor(),
        );

        self::assertSame(
            $sortBy,
            $query->getSortBy(),
        );

        self::assertSame(
            $toString,
            (string) $query,
        );
    }

    public function testSimpleFromRemoteIds(): void
    {
        $query = Query::fromRemoteIds(['test/image.jpg']);

        self::assertNull($query->getQuery());
        self::assertEmpty($query->getTypes());
        self::assertEmpty($query->getFolders());
        self::assertEmpty($query->getVisibilities());
        self::assertEmpty($query->getTags());
        self::assertEmpty($query->getContext());

        self::assertSame(
            ['test/image.jpg'],
            $query->getRemoteIds(),
        );

        self::assertSame(
            25,
            $query->getLimit(),
        );

        self::assertNull($query->getNextCursor());

        self::assertSame(
            ['created_at' => 'desc'],
            $query->getSortBy(),
        );

        self::assertSame(
            '|25||||||test/image.jpg||created_at=desc',
            (string) $query,
        );
    }

    public function testSettingNextCursor(): void
    {
        $query = new Query(query: 'tech');

        self::assertNull($query->getNextCursor());

        $query->setNextCursor('f439t04h32dsf3dsfewf');

        self::assertSame(
            'f439t04h32dsf3dsfewf',
            $query->getNextCursor(),
        );
    }

    public static function constructorPropsProvider(): array
    {
        return [
            [
                'test search',
                [],
                [],
                [],
                [],
                [],
                [],
                25,
                null,
                ['created_at' => 'desc'],
                'test search|25||||||||created_at=desc',
            ],
            [
                null,
                ['audio', 'video'],
                ['root/images', 'root/videos'],
                ['public', 'private'],
                ['tag1', 'tag2', 'tag3'],
                [],
                [
                    'type' => ['product_image', 'product_category'],
                    'source' => 'webshop',
                ],
                30,
                null,
                ['updated_at' => 'asc'],
                '|30||audio,video|root/images,root/videos|public,private|tag1,tag2,tag3||type=product_image,type=product_category,source=webshop|updated_at=asc',
            ],
            [
                null,
                [],
                [],
                [],
                [],
                ['root/images/image1.jpg', 'root/videos/example.mp4'],
                [],
                25,
                null,
                ['created_at' => 'desc'],
                '|25||||||root/images/image1.jpg,root/videos/example.mp4||created_at=desc',
            ],
            [
                'unix',
                ['image'],
                ['root/images'],
                ['protected'],
                ['tech'],
                [],
                [],
                25,
                'd395jdgew45nd73kjsijfh',
                ['created_at' => 'asc'],
                'unix|25|d395jdgew45nd73kjsijfh|image|root/images|protected|tech|||created_at=asc',
            ],
        ];
    }

    public static function remoteIdsProvider(): array
    {
        return [
            [
                ['image.jpg', 'test/subfolder/document.pdf', 'videos/example.mp4', 'media/audio/song.mp3'],
                100,
                null,
                ['created_at' => 'asc'],
                '|100||||||image.jpg,test/subfolder/document.pdf,videos/example.mp4,media/audio/song.mp3||created_at=asc',
            ],
            [
                ['image.jpg'],
                25,
                'ewdsofu439oirejfoi3',
                ['updated_at' => 'desc'],
                '|25|ewdsofu439oirejfoi3|||||image.jpg||updated_at=desc',
            ],
        ];
    }
}
