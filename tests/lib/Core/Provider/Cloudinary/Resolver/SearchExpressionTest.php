<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression as SearchExpressionResolver;
use PHPUnit\Framework\TestCase;

final class SearchExpressionTest extends TestCase
{
    protected SearchExpressionResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SearchExpressionResolver(
            new ResourceTypeConverter(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolve
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolveFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolveResourceIds
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolveResourceTypes
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolveSearchQuery
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression::resolveTags
     * @dataProvider dataProvider
     */
    public function testResolve(Query $query, string $expression): void
    {
        self::assertSame(
            $expression,
            $this->resolver->resolve($query),
        );
    }

    public function dataProvider(): array
    {
        return [
            [
                new Query(),
                '',
            ],
            [
                new Query([
                    'query' => 'search term',
                ]),
                '*search term*',
            ],
            [
                new Query([
                    'folders' => ['root/images/1'],
                ]),
                '(folder:"root/images/1")',
            ],
            [
                new Query([
                    'folders' => ['root/images/1', 'root/videos/2'],
                ]),
                '(folder:"root/images/1" OR folder:"root/videos/2")',
            ],
            [
                new Query([
                    'remoteIds' => ['upload|image|root/test/picture1', 'upload|image|root/test/picture2', 'upload|image|root/test/picture3'],
                ]),
                '(public_id:"root/test/picture1" OR public_id:"root/test/picture2" OR public_id:"root/test/picture3")',
            ],
            [
                new Query([
                    'types' => ['video'],
                ]),
                '(resource_type:"video")',
            ],
            [
                new Query([
                    'types' => ['video', 'image', 'raw'],
                ]),
                '(resource_type:"video" OR resource_type:"image" OR resource_type:"raw")',
            ],
            [
                new Query([
                    'tags' => ['tech'],
                ]),
                '(tags:"tech")',
            ],
            [
                new Query([
                    'tags' => ['tech', 'nature'],
                ]),
                '(tags:"tech" OR tags:"nature")',
            ],
            [
                new Query([
                    'query' => 'android',
                    'types' => ['video'],
                    'folders' => ['root/videos'],
                    'tags' => ['tech'],
                ]),
                '(resource_type:"video") AND *android* AND (folder:"root/videos") AND (tags:"tech")',
            ],
            [
                new Query([
                    'query' => 'search term',
                    'types' => ['image', 'video', 'document', 'audio'],
                    'folders' => ['root', 'root/test'],
                    'tags' => ['tech', 'nature'],
                    'remoteIds' => ['upload|image|root/test/picture1', 'upload|image|root/test/picture2', 'upload|image|root/test/picture3'],
                    'limit' => 30,
                    'nextCursor' => 'ko5mjv8205hupoew3',
                    'sortBy' => ['created_at' => 'asc'],
                ]),
                '(resource_type:"image" OR resource_type:"video")'
                . ' AND *search term*'
                . ' AND (folder:"root" OR folder:"root/test")'
                . ' AND (tags:"tech" OR tags:"nature")'
                . ' AND (public_id:"root/test/picture1" OR public_id:"root/test/picture2" OR public_id:"root/test/picture3")',
            ],
        ];
    }
}
