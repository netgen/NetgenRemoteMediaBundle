<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Search;

use Netgen\RemoteMedia\API\Search\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\API\Search\Query::__construct
     * @covers \Netgen\RemoteMedia\API\Search\Query::__toString
     * @covers \Netgen\RemoteMedia\API\Search\Query::getContext
     * @covers \Netgen\RemoteMedia\API\Search\Query::getFolders
     * @covers \Netgen\RemoteMedia\API\Search\Query::getLimit
     * @covers \Netgen\RemoteMedia\API\Search\Query::getNextCursor
     * @covers \Netgen\RemoteMedia\API\Search\Query::getQuery
     * @covers \Netgen\RemoteMedia\API\Search\Query::getRemoteIds
     * @covers \Netgen\RemoteMedia\API\Search\Query::getSortBy
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTags
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTypes
     * @covers \Netgen\RemoteMedia\API\Search\Query::getVisibilities
     *
     * @dataProvider constructorPropsProvider
     */
    public function testConstructor(
        array $props,
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
        $query = new Query($props);

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

    /**
     * @covers \Netgen\RemoteMedia\API\Search\Query::__construct
     * @covers \Netgen\RemoteMedia\API\Search\Query::__toString
     * @covers \Netgen\RemoteMedia\API\Search\Query::fromRemoteIds
     * @covers \Netgen\RemoteMedia\API\Search\Query::getContext
     * @covers \Netgen\RemoteMedia\API\Search\Query::getFolders
     * @covers \Netgen\RemoteMedia\API\Search\Query::getLimit
     * @covers \Netgen\RemoteMedia\API\Search\Query::getNextCursor
     * @covers \Netgen\RemoteMedia\API\Search\Query::getQuery
     * @covers \Netgen\RemoteMedia\API\Search\Query::getRemoteIds
     * @covers \Netgen\RemoteMedia\API\Search\Query::getSortBy
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTags
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTypes
     * @covers \Netgen\RemoteMedia\API\Search\Query::getVisibilities
     *
     * @dataProvider remoteIdsProvider
     */
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

    /**
     * @covers \Netgen\RemoteMedia\API\Search\Query::__construct
     * @covers \Netgen\RemoteMedia\API\Search\Query::__toString
     * @covers \Netgen\RemoteMedia\API\Search\Query::getContext
     * @covers \Netgen\RemoteMedia\API\Search\Query::getFolders
     * @covers \Netgen\RemoteMedia\API\Search\Query::getLimit
     * @covers \Netgen\RemoteMedia\API\Search\Query::getNextCursor
     * @covers \Netgen\RemoteMedia\API\Search\Query::getQuery
     * @covers \Netgen\RemoteMedia\API\Search\Query::getRemoteIds
     * @covers \Netgen\RemoteMedia\API\Search\Query::getSortBy
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTags
     * @covers \Netgen\RemoteMedia\API\Search\Query::getTypes
     * @covers \Netgen\RemoteMedia\API\Search\Query::getVisibilities
     */
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

    /**
     * @covers \Netgen\RemoteMedia\API\Search\Query::__construct
     * @covers \Netgen\RemoteMedia\API\Search\Query::getNextCursor
     * @covers \Netgen\RemoteMedia\API\Search\Query::setNextCursor
     */
    public function testSettingNextCursor(): void
    {
        $query = new Query(['query' => 'tech']);

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
                [
                    'query' => 'test search',
                ],
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
                [
                    'types' => ['audio', 'video'],
                    'folders' => ['root/images', 'root/videos'],
                    'visibilities' => ['public', 'private'],
                    'tags' => ['tag1', 'tag2', 'tag3'],
                    'limit' => 30,
                    'sortBy' => ['updated_at' => 'asc'],
                    'context' => ['type' => ['product_image', 'product_category'], 'source' => 'webshop'],
                ],
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
                [
                    'remoteIds' => ['root/images/image1.jpg', 'root/videos/example.mp4'],
                ],
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
                [
                    'query' => 'unix',
                    'types' => ['image'],
                    'folders' => ['root/images'],
                    'visibilities' => ['protected'],
                    'tags' => ['tech'],
                    'nextCursor' => 'd395jdgew45nd73kjsijfh',
                    'sortBy' => ['created_at' => 'asc'],
                ],
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
