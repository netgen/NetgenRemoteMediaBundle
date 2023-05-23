<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway\Cache;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

use function array_values;
use function count;
use function sprintf;

#[CoversClass(Psr6CachedGateway::class)]
final class Psr6CachedGatewayTest extends AbstractTestCase
{
    private const CACHE_TTL = 7200;

    protected Psr6CachedGateway $taggableCachedGateway;

    protected Psr6CachedGateway $nonTaggableCachedGateway;

    protected MockObject|GatewayInterface $apiGatewayMock;

    protected CacheItemPoolInterface $taggableCache;

    protected CacheItemPoolInterface $nonTaggableCache;

    protected function setUp(): void
    {
        $this->apiGatewayMock = self::createMock(GatewayInterface::class);

        $this->taggableCache = new TagAwareAdapter(new ArrayAdapter());
        $this->nonTaggableCache = new ArrayAdapter();

        $this->taggableCachedGateway = new Psr6CachedGateway(
            $this->apiGatewayMock,
            $this->taggableCache,
            self::CACHE_TTL,
        );

        $this->nonTaggableCachedGateway = new Psr6CachedGateway(
            $this->apiGatewayMock,
            $this->nonTaggableCache,
            self::CACHE_TTL,
        );
    }

    public function testUsage(): void
    {
        $usageData = new StatusData([
            'plan' => 'Advanced',
            'limit' => 1000,
            'remaining_limit' => 990,
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('usage')
            ->willReturn($usageData);

        $result = $this->taggableCachedGateway->usage();

        self::assertInstanceOf(
            StatusData::class,
            $result,
        );

        self::assertCount(
            count($usageData->all()),
            $result->all(),
        );
    }

    public function testIsEncryptionEnabled(): void
    {
        $this->apiGatewayMock
            ->expects(self::exactly(2))
            ->method('isEncryptionEnabled')
            ->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->taggableCachedGateway->isEncryptionEnabled());
        self::assertFalse($this->taggableCachedGateway->isEncryptionEnabled());
    }

    public function testCountResourcesCached(): void
    {
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count');
        $cacheItem->set(500);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertSame(
            500,
            $this->taggableCachedGateway->countResources(),
        );
    }

    public function testCountResourcesNonCachedTaggable(): void
    {
        $this->taggableCache->deleteItem('ngremotemedia-cloudinary-resources_count');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(500);

        self::assertSame(
            500,
            $this->taggableCachedGateway->countResources(),
        );

        $tags = ['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-resources_count'];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testCountResourcesNonCachedNonTaggable(): void
    {
        $this->nonTaggableCache->deleteItem('ngremotemedia-cloudinary-resources_count');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(500);

        self::assertSame(
            500,
            $this->nonTaggableCachedGateway->countResources(),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resources_count');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testCountResourcesInFolderCached(): void
    {
        $folder = 'test/subtest';

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest');
        $cacheItem->set(200);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertSame(
            200,
            $this->taggableCachedGateway->countResourcesInFolder($folder),
        );
    }

    public function testCountResourcesInFolderNonCachedTaggable(): void
    {
        $folder = 'test/subtest';

        $this->taggableCache->delete('ngremotemedia-cloudinary-folder_count-test_subtest');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with($folder)
            ->willReturn(200);

        self::assertSame(
            200,
            $this->taggableCachedGateway->countResourcesInFolder($folder),
        );

        $tags = ['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_count'];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testCountResourcesInFolderNonCachedNonTaggable(): void
    {
        $folder = 'test/subtest';

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-folder_count-test_subtest');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with($folder)
            ->willReturn(200);

        self::assertSame(
            200,
            $this->nonTaggableCachedGateway->countResourcesInFolder($folder),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testListFoldersCached(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list');
        $cacheItem->set($folders);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertSame(
            $folders,
            $this->taggableCachedGateway->listFolders(),
        );
    }

    public function testListFoldersNonCachedTaggable(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $this->taggableCache->delete('ngremotemedia-cloudinary-folder_list');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        self::assertSame(
            $folders,
            $this->taggableCachedGateway->listFolders(),
        );

        $tags = ['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list'];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testListFoldersNonCachedNonTaggable(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-folder_list');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        self::assertSame(
            $folders,
            $this->nonTaggableCachedGateway->listFolders(),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testListSubFoldersCached(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test');
        $cacheItem->set($subFolders);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertSame(
            $subFolders,
            $this->taggableCachedGateway->listSubFolders($folder),
        );
    }

    public function testListSubFoldersNonCachedTaggable(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $this->taggableCache->delete('ngremotemedia-cloudinary-folder_list-test');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($folder)
            ->willReturn($subFolders);

        self::assertSame(
            $subFolders,
            $this->taggableCachedGateway->listSubFolders($folder),
        );

        $tags = ['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list'];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testListSubFoldersNonCachedNonTaggable(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-folder_list-test');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($folder)
            ->willReturn($subFolders);

        self::assertSame(
            $subFolders,
            $this->nonTaggableCachedGateway->listSubFolders($folder),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list-test');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testCreateFolder(): void
    {
        $path = 'test/subfolder/newfolder';

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('createFolder')
            ->with($path);

        $this->taggableCachedGateway->createFolder($path);
    }

    public function testGetCached(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $remoteResource = new RemoteResource(
            remoteId: $remoteId->getRemoteId(),
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
            metadata: ['format' => 'jpg'],
        );

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');
        $cacheItem->set($remoteResource);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->taggableCachedGateway->get($remoteId),
        );
    }

    public function testGetNonCachedTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $remoteResource = new RemoteResource(
            remoteId: $remoteId->getRemoteId(),
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
            metadata: ['format' => 'jpg'],
        );

        $this->taggableCache->delete('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willReturn($remoteResource);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->taggableCachedGateway->get($remoteId),
        );

        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg',
        ];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testGetNonCachedNonTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $remoteResource = new RemoteResource(
            remoteId: $remoteId->getRemoteId(),
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
            metadata: ['format' => 'jpg'],
        );

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willReturn($remoteResource);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->nonTaggableCachedGateway->get($remoteId),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testGetNotFound(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willThrowException(new RemoteResourceNotFoundException($remoteId->getRemoteId()));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage(sprintf('Remote resource with ID "%s" not found.', $remoteId->getRemoteId()));

        $this->nonTaggableCachedGateway->get($remoteId);
    }

    public function testUpload(): void
    {
        $fileUri = 'test_image.jpg';
        $options = [
            'type' => 'upload',
            'resource_type' => 'auto',
        ];

        $resource = new RemoteResource(
            remoteId: 'upload|image|test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
        );

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('upload')
            ->with($fileUri, $options)
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->nonTaggableCachedGateway->upload($fileUri, $options),
        );
    }

    public function testUpdate(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');
        $options = ['tags' => ['new_tag']];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('update')
            ->with($remoteId, $options);

        $this->nonTaggableCachedGateway->update($remoteId, $options);
    }

    public function testRemoveAllTagsFromResource(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with($remoteId);

        $this->nonTaggableCachedGateway->removeAllTagsFromResource($remoteId);
    }

    public function testDelete(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('delete')
            ->with($remoteId);

        $this->nonTaggableCachedGateway->delete($remoteId);
    }

    public function testGetAuthenticatedUrl(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');
        $token = AuthToken::fromExpiresAt(new DateTimeImmutable('2023/1/1'));
        $url = 'https://res.cloudinary.com/testcloud/image/upload/folder/test_image.jpg?__cld_token__=exp=1672527600~hmac=81c6ab1a5bde49cdc3a1fe73bf504d7daf23b23b699cb386f551a0c2d4bd9ac8';

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getAuthenticatedUrl')
            ->with($remoteId, $token)
            ->willReturn($url);

        self::assertSame(
            $url,
            $this->taggableCachedGateway->getAuthenticatedUrl($remoteId, $token),
        );
    }

    public function testGetVariationUrl(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');
        $transformations = [
            'x' => 50,
            'y' => 50,
            'width' => 300,
            'height' => 200,
            'crop' => 'crop',
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getVariationUrl')
            ->with($remoteId, $transformations)
            ->willReturn('https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/folder/test_image.jpg');

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/folder/test_image.jpg',
            $this->taggableCachedGateway->getVariationUrl($remoteId, $transformations),
        );
    }

    public function testSearchCached(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
        );

        $searchResult = new Result(200, '123', [$resource]);

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1||||created_at=desc');
        $cacheItem->set($searchResult);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        self::assertSearchResultSame(
            $searchResult,
            $this->taggableCachedGateway->search($query),
        );
    }

    public function testSearchNonCachedTaggable(): void
    {
        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
        );

        $searchResult = new Result(200, '123', [$resource]);

        $this->taggableCache->delete('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1||||created_at=desc');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        self::assertSearchResultSame(
            $searchResult,
            $this->taggableCachedGateway->search($query),
        );

        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-search',
        ];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1||||created_at=desc');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testSearchNonCachedNonTaggable(): void
    {
        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
        );

        $searchResult = new Result(200, '123', [$resource]);

        $this->taggableCache->delete('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1||||created_at=desc');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        self::assertSearchResultSame(
            $searchResult,
            $this->nonTaggableCachedGateway->search($query),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1||||created_at=desc');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testSearchCountCached(): void
    {
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1||||created_at=desc');
        $cacheItem->set(50);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        self::assertSame(
            50,
            $this->taggableCachedGateway->searchCount($query),
        );
    }

    public function testSearchCountNonCachedTaggable(): void
    {
        $this->taggableCache->delete('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||type=product_image,type=category_image|created_at=desc');

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
            context: ['type' => ['product_image', 'category_image']],
        );

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(50);

        self::assertSame(
            50,
            $this->taggableCachedGateway->searchCount($query),
        );

        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-search_count',
        ];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||type=product_image,type=category_image|created_at=desc');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testSearchCountNonCachedNonTaggable(): void
    {
        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1||||created_at=desc');

        $query = new Query(
            query: 'test',
            types: ['image', 'video'],
            folders: ['test_folder'],
            tags: ['tag1'],
        );

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(50);

        self::assertSame(
            50,
            $this->nonTaggableCachedGateway->searchCount($query),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1||||created_at=desc');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testListTagsCached(): void
    {
        $tags = ['tag1', 'tag2'];

        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list');
        $cacheItem->set($tags);
        $cacheItem->expiresAfter(1000);

        $this->taggableCache->save($cacheItem);

        self::assertSame(
            $tags,
            $this->taggableCachedGateway->listTags(),
        );
    }

    public function testListTagsNonCachedTaggable(): void
    {
        $tags = ['tag1', 'tag2'];

        $this->taggableCache->delete('ngremotemedia-cloudinary-tag_list');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        self::assertSame(
            $tags,
            $this->taggableCachedGateway->listTags(),
        );

        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-tag_list',
        ];
        $cacheItem = $this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list');

        self::assertTrue($cacheItem->isHit());
        self::assertSame($tags, array_values($cacheItem->getMetadata()['tags']));
    }

    public function testListTagsNonCachedNonTaggable(): void
    {
        $tags = ['tag1', 'tag2'];

        $this->nonTaggableCache->delete('ngremotemedia-cloudinary-tag_list');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        self::assertSame(
            $tags,
            $this->nonTaggableCachedGateway->listTags(),
        );

        $cacheItem = $this->nonTaggableCache->getItem('ngremotemedia-cloudinary-tag_list');

        self::assertTrue($cacheItem->isHit());
        self::assertEmpty(array_values($cacheItem->getMetadata()['tags'] ?? []));
    }

    public function testGetVideoThumbnail(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|video|example.mp4');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with($cloudinaryId)
            ->willReturn('video_thumbnail.jpg');

        self::assertSame(
            'video_thumbnail.jpg',
            $this->taggableCachedGateway->getVideoThumbnail($cloudinaryId),
        );
    }

    public function testGetImageTag(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|image|image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getImageTag')
            ->with($cloudinaryId)
            ->willReturn('<img src="image.jpg"/>');

        self::assertSame(
            '<img src="image.jpg"/>',
            $this->taggableCachedGateway->getImageTag($cloudinaryId),
        );
    }

    public function testGetVideoTag(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|video|example.mp4');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getVideoTag')
            ->with($cloudinaryId)
            ->willReturn('<video src="example.mp4"/>');

        self::assertSame(
            '<video src="example.mp4"/>',
            $this->taggableCachedGateway->getVideoTag($cloudinaryId),
        );
    }

    public function testGetDownloadLink(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|raw|test.zip');

        $options = [
            'transformations' => [],
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with($cloudinaryId, $options)
            ->willReturn('https://cloudinary.com/test.zip');

        self::assertSame(
            'https://cloudinary.com/test.zip',
            $this->taggableCachedGateway->getDownloadLink($cloudinaryId, $options),
        );
    }

    public function testInvalidateResourceListCache(): void
    {
        $this->prepareCacheForInvalidationTest($this->taggableCache);

        $this->taggableCachedGateway->invalidateResourceListCache();

        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateResourceListCacheNonTaggable(): void
    {
        $this->prepareCacheForInvalidationTest($this->nonTaggableCache);

        $this->nonTaggableCachedGateway->invalidateResourceListCache();

        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateResourceCache(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $this->prepareCacheForInvalidationTest($this->taggableCache);

        $this->taggableCachedGateway->invalidateResourceCache($remoteId);

        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateResourceCacheNonTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $this->prepareCacheForInvalidationTest($this->nonTaggableCache);

        $this->nonTaggableCachedGateway->invalidateResourceCache($remoteId);

        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateFoldersCache(): void
    {
        $this->prepareCacheForInvalidationTest($this->taggableCache);

        $this->taggableCachedGateway->invalidateFoldersCache();

        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateFoldersCacheNonTaggable(): void
    {
        $this->prepareCacheForInvalidationTest($this->nonTaggableCache);

        $this->nonTaggableCachedGateway->invalidateFoldersCache();

        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateTagsCache(): void
    {
        $this->prepareCacheForInvalidationTest($this->taggableCache);

        $this->taggableCachedGateway->invalidateTagsCache();

        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->taggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertFalse($this->taggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    public function testInvalidateTagsCacheNonTaggable(): void
    {
        $this->prepareCacheForInvalidationTest($this->nonTaggableCache);

        $this->nonTaggableCachedGateway->invalidateTagsCache();

        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resources_count')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_count-media')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-folder_list-test')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc')->isHit());
        self::assertTrue($this->nonTaggableCache->getItem('ngremotemedia-cloudinary-tag_list')->isHit());
    }

    private function prepareCacheForInvalidationTest(CacheItemPoolInterface $cache): void
    {
        $cache->deleteItem('ngremotemedia-cloudinary-resources_count');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-resources_count');
        $cacheItem->set(500);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-resources_count']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-folder_count-test_subtest');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-folder_count-test_subtest');
        $cacheItem->set(200);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-folder_count-media');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-folder_count-media');
        $cacheItem->set(30);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-folder_list');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-folder_list');
        $cacheItem->set(['test', 'test2']);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-folder_list-test');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-folder_list-test');
        $cacheItem->set(['subfolder']);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg');

        $cacheItem->set(
            new RemoteResource(
                remoteId: 'upload|image|folder/test_image.jpg',
                type: RemoteResource::TYPE_IMAGE,
                url: 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
                md5: 'a522f23sf81aa0afd03387c37e2b6eax',
                name: 'test_image.jpg',
                metadata: ['format' => 'jpg'],
            ),
        );

        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-search-test|25||image,video|test_folder||tag1|||created_at=desc');

        $cacheItem->set(
            new Result(200, '123', [
                new RemoteResource(
                    remoteId: 'upload|image|test_image.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/image/test_image.jpg',
                    md5: 'a522f23sf81aa0afd03387c37e2b6eax',
                    name: 'test_image.jpg',
                ),
            ]),
        );

        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-search']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder||tag1|||created_at=desc');
        $cacheItem->set(50);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-search']);
        }

        $cache->save($cacheItem);

        $cache->deleteItem('ngremotemedia-cloudinary-tag_list');
        $cacheItem = $cache->getItem('ngremotemedia-cloudinary-tag_list');
        $cacheItem->set(['tag1', 'tag2']);
        $cacheItem->expiresAfter(1000);

        if ($cache instanceof TagAwareAdapterInterface) {
            $cacheItem->tag(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-tag_list']);
        }

        $cache->save($cacheItem);
    }
}
