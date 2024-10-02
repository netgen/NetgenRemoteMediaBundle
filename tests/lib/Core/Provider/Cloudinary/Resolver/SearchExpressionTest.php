<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression as SearchExpressionResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchExpressionResolver::class)]
final class SearchExpressionTest extends TestCase
{
    protected SearchExpressionResolver $fixedFolderModeResolver;

    protected SearchExpressionResolver $dynamicFolderModeResolver;

    protected function setUp(): void
    {
        $this->fixedFolderModeResolver = new SearchExpressionResolver(
            new ResourceTypeConverter(),
            new VisibilityTypeConverter(),
            CloudinaryProvider::FOLDER_MODE_FIXED,
        );

        $this->dynamicFolderModeResolver = new SearchExpressionResolver(
            new ResourceTypeConverter(),
            new VisibilityTypeConverter(),
            CloudinaryProvider::FOLDER_MODE_DYNAMIC,
        );
    }

    #[DataProvider('dataProvider')]
    public function testResolve(Query $query, string $expression, string $folderMode = CloudinaryProvider::FOLDER_MODE_FIXED): void
    {
        $actualExpression = $folderMode === CloudinaryProvider::FOLDER_MODE_FIXED
            ? $this->fixedFolderModeResolver->resolve($query)
            : $this->dynamicFolderModeResolver->resolve($query);

        self::assertSame(
            $expression,
            $actualExpression,
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                new Query(),
                '',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(query: 'search term'),
                'search term*',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(folders: [Folder::fromPath('root/images/1')]),
                '(asset_folder:"root/images/1")',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(
                    folders: [Folder::fromPath('root/images/1'), Folder::fromPath('root/videos/2')],
                    context: ['type' => ['product_image', 'category_image'], 'source' => 'user_upload'],
                ),
                '(folder:"root/images/1" OR folder:"root/videos/2") AND ((context.type="product_image" OR context.type="category_image") AND (context.source="user_upload"))',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(
                    folders: [Folder::fromPath('root/images/1'), Folder::fromPath('root/videos/2')],
                    context: ['type' => ['product_image', 'category_image'], 'source' => 'user_upload'],
                ),
                '(asset_folder:"root/images/1" OR asset_folder:"root/videos/2") AND ((context.type="product_image" OR context.type="category_image") AND (context.source="user_upload"))',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(remoteIds: ['upload|image|root/test/picture1', 'upload|image|root/test/picture2', 'upload|image|root/test/picture3']),
                '(public_id:"root/test/picture1" OR public_id:"root/test/picture2" OR public_id:"root/test/picture3")',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(md5s: ['hash1', 'hash2']),
                '(etag="hash1" OR etag="hash2")',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(types: ['video']),
                '(resource_type:"video")'
                . ' AND (((!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac")'
                . ' AND (!format="m4a") AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(types: ['document']),
                '(resource_type:"image" OR resource_type:"raw")'
                . ' AND ((format="pdf" OR format="doc" OR format="docx" OR format="ppt" OR format="pptx" OR format="txt"))',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(types: ['document', 'raw']),
                '(resource_type:"image" OR resource_type:"raw")'
                . ' AND ((format="pdf" OR format="doc" OR format="docx" OR format="ppt" OR format="pptx" OR format="txt"))',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(
                    types: ['video', 'image', 'raw'],
                    context: ['source' => 'user_upload'],
                ),
                '(resource_type:"video" OR resource_type:"image" OR resource_type:"raw")'
                . ' AND (((!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac") AND (!format="m4a")'
                . ' AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav") AND (!format="pdf")'
                . ' AND (!format="doc") AND (!format="docx") AND (!format="ppt") AND (!format="pptx") AND (!format="txt")))'
                . ' AND ((context.source="user_upload"))',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(visibilities: ['public']),
                '(type:"upload")',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(visibilities: ['protected']),
                '(type:"authenticated")',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(tags: ['tech']),
                '(tags:"tech")',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(tags: ['tech', 'nature']),
                '(tags:"tech" OR tags:"nature")',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(
                    query: 'android',
                    types: ['video'],
                    folders: [Folder::fromPath('root/videos')],
                    visibilities: ['public'],
                    tags: ['tech'],
                ),
                '(resource_type:"video")'
                . ' AND (((!format="aac") AND (!format="aiff") AND (!format="amr") AND (!format="flac") AND (!format="m4a")'
                . ' AND (!format="mp3") AND (!format="ogg") AND (!format="opus") AND (!format="wav")))'
                . ' AND android*'
                . ' AND (folder:"root/videos")'
                . ' AND (type:"upload")'
                . ' AND (tags:"tech")',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
            [
                new Query(
                    query: 'podcast',
                    types: ['audio'],
                    folders: [Folder::fromPath('root/audio')],
                    visibilities: ['protected'],
                ),
                '(resource_type:"video")'
                . ' AND ((format="aac" OR format="aiff" OR format="amr" OR format="flac" OR format="m4a"'
                . ' OR format="mp3" OR format="ogg" OR format="opus" OR format="wav"))'
                . ' AND podcast*'
                . ' AND (asset_folder:"root/audio")'
                . ' AND (type:"authenticated")',
                CloudinaryProvider::FOLDER_MODE_DYNAMIC,
            ],
            [
                new Query(
                    query: 'search term',
                    types: ['image', 'video', 'document', 'audio'],
                    folders: [Folder::fromPath('root'), Folder::fromPath('root/test')],
                    tags: ['tech', 'nature'],
                    remoteIds: ['upload|image|root/test/picture1', 'upload|image|root/test/picture2', 'upload|image|root/test/picture3'],
                    md5s: ['hash1', 'hash2'],
                    context: ['original_filename' => 'picture_*', 'type' => ['product_image', 'category_image']],
                    limit: 30,
                    nextCursor: 'ko5mjv8205hupoew3',
                    sortBy: ['created_at' => 'asc'],
                ),
                '(resource_type:"image" OR resource_type:"video" OR resource_type:"raw")'
                . ' AND search term*'
                . ' AND (folder:"root" OR folder:"root/test")'
                . ' AND (tags:"tech" OR tags:"nature")'
                . ' AND (public_id:"root/test/picture1" OR public_id:"root/test/picture2" OR public_id:"root/test/picture3")'
                . ' AND (etag="hash1" OR etag="hash2")'
                . ' AND ((context.original_filename="picture_*") AND (context.type="product_image" OR context.type="category_image"))',
                CloudinaryProvider::FOLDER_MODE_FIXED,
            ],
        ];
    }
}
