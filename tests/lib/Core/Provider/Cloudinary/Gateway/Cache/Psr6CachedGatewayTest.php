<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway\Cache;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

use function count;
use function sprintf;

final class Psr6CachedGatewayTest extends AbstractTest
{
    private const CACHE_TTL = 7200;

    protected Psr6CachedGateway $taggableCachedGateway;

    protected Psr6CachedGateway $nonTaggableCachedGateway;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface
     */
    protected MockObject $apiGatewayMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    protected MockObject $taggableCacheMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Cache\CacheItemPoolInterface
     */
    protected MockObject $nonTaggableCacheMock;

    protected function setUp(): void
    {
        $this->apiGatewayMock = self::createMock(GatewayInterface::class);
        $this->taggableCacheMock = self::createMock(TagAwareAdapterInterface::class);
        $this->nonTaggableCacheMock = self::createMock(CacheItemPoolInterface::class);

        $this->taggableCachedGateway = new Psr6CachedGateway(
            $this->apiGatewayMock,
            $this->taggableCacheMock,
            self::CACHE_TTL,
        );

        $this->nonTaggableCachedGateway = new Psr6CachedGateway(
            $this->apiGatewayMock,
            $this->nonTaggableCacheMock,
            self::CACHE_TTL,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::usage
     */
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

        self::assertSame(
            count($usageData->all()),
            count($result->all()),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResources
     */
    public function testCountResourcesCached(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resources_count')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn(500);

        self::assertSame(
            500,
            $this->taggableCachedGateway->countResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResources
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testCountResourcesNonCachedTaggable(): void
    {
        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resources_count')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(500);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(500);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-resources_count']);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            500,
            $this->taggableCachedGateway->countResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResources
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testCountResourcesNonCachedNonTaggable(): void
    {
        $cacheItem = self::createMock(ItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resources_count')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(500);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(500);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            500,
            $this->nonTaggableCachedGateway->countResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResourcesInFolder
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testCountResourcesInFolderCached(): void
    {
        $folder = 'test/subtest';

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_count-test_subtest')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn(200);

        self::assertSame(
            200,
            $this->taggableCachedGateway->countResourcesInFolder($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResourcesInFolder
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testCountResourcesInFolderNonCachedTaggable(): void
    {
        $folder = 'test/subtest';

        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_count-test_subtest')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with($folder)
            ->willReturn(200);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(200);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_count']);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            200,
            $this->taggableCachedGateway->countResourcesInFolder($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::countResourcesInFolder
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testCountResourcesInFolderNonCachedNonTaggable(): void
    {
        $folder = 'test/subtest';

        $cacheItem = self::createMock(ItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_count-test_subtest')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with($folder)
            ->willReturn(200);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(200);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            200,
            $this->nonTaggableCachedGateway->countResourcesInFolder($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListFoldersCached(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($folders);

        self::assertSame(
            $folders,
            $this->taggableCachedGateway->listFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListFoldersCachedTaggable(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($folders);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);

        self::assertSame(
            $folders,
            $this->taggableCachedGateway->listFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListFoldersCachedNonTaggable(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $cacheItem = self::createMock(ItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($folders);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        self::assertSame(
            $folders,
            $this->nonTaggableCachedGateway->listFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listSubFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListSubFoldersCached(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list-test')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($subFolders);

        self::assertSame(
            $subFolders,
            $this->taggableCachedGateway->listSubFolders($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listSubFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListSubFoldersCachedTaggable(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list-test')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($folder)
            ->willReturn($subFolders);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($subFolders);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with(['ngremotemedia-cloudinary', 'ngremotemedia-cloudinary-folder_list']);

        self::assertSame(
            $subFolders,
            $this->taggableCachedGateway->listSubFolders($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listSubFolders
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListSubFoldersCachedNonTaggable(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $cacheItem = self::createMock(ItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-folder_list-test')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($folder)
            ->willReturn($subFolders);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($subFolders);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        self::assertSame(
            $subFolders,
            $this->nonTaggableCachedGateway->listSubFolders($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::createFolder
     */
    public function testCreateFolder(): void
    {
        $path = 'test/subfolder/newfolder';

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('createFolder')
            ->with($path);

        $this->taggableCachedGateway->createFolder($path);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::get
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testGetCached(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $remoteResource = new RemoteResource([
            'remoteId' => $remoteId->getRemoteId(),
            'type' => RemoteResource::TYPE_IMAGE,
            'url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
            'metadata' => [
                'format' => 'jpg',
            ],
        ]);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($remoteResource);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->taggableCachedGateway->get($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::get
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getItemCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testGetNonCachedTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $remoteResource = new RemoteResource([
            'remoteId' => $remoteId->getRemoteId(),
            'type' => RemoteResource::TYPE_IMAGE,
            'url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
            'metadata' => [
                'format' => 'jpg',
            ],
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willReturn($remoteResource);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($remoteResource);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with([
                'ngremotemedia-cloudinary',
                'ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg',
            ]);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->taggableCachedGateway->get($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::get
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testGetNonCachedNonTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $remoteResource = new RemoteResource([
            'remoteId' => $remoteId->getRemoteId(),
            'type' => RemoteResource::TYPE_IMAGE,
            'url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
            'metadata' => [
                'format' => 'jpg',
            ],
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willReturn($remoteResource);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($remoteResource);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->nonTaggableCachedGateway->get($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::get
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testGetNotFound(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willThrowException(new RemoteResourceNotFoundException($remoteId->getRemoteId()));

        self::expectException(RemoteResourceNotFoundException::class);
        self::expectExceptionMessage(sprintf('Remote resource with ID "%s" not found.', $remoteId->getRemoteId()));

        $this->nonTaggableCachedGateway->get($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::upload
     */
    public function testUpload(): void
    {
        $fileUri = 'test_image.jpg';
        $options = [
            'type' => 'upload',
            'resource_type' => 'auto',
        ];

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|test_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::update
     */
    public function testUpdate(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');
        $options = [
            'tags' => ['new_tag'],
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('update')
            ->with($remoteId, $options);

        $this->nonTaggableCachedGateway->update($remoteId, $options);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::removeAllTagsFromResource
     */
    public function testRemoveAllTagsFromResource(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with($remoteId);

        $this->nonTaggableCachedGateway->removeAllTagsFromResource($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::delete
     */
    public function testDelete(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('delete')
            ->with($remoteId);

        $this->nonTaggableCachedGateway->delete($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getVariationUrl
     */
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
            ->willReturn('https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/v1/folder/test_image.jpg');

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/v1/folder/test_image.jpg',
            $this->taggableCachedGateway->getVariationUrl($remoteId, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::search
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchCached(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|test_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $searchResult = new Result(200, '123', [$resource]);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($searchResult);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        self::assertSearchResultSame(
            $searchResult,
            $this->taggableCachedGateway->search($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::search
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchNonCachedTaggable(): void
    {
        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|test_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $searchResult = new Result(200, '123', [$resource]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($searchResult);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with([
                'ngremotemedia-cloudinary',
                'ngremotemedia-cloudinary-search',
            ]);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSearchResultSame(
            $searchResult,
            $this->taggableCachedGateway->search($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::search
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchNonCachedNonTaggable(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|test_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $searchResult = new Result(200, '123', [$resource]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($searchResult);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSearchResultSame(
            $searchResult,
            $this->nonTaggableCachedGateway->search($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::searchCount
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchCountCached(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn(50);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        self::assertSame(
            50,
            $this->taggableCachedGateway->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::searchCount
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchCountNonCachedTaggable(): void
    {
        $cacheItem = self::createMock(ItemInterface::class);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(50);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(50);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with([
                'ngremotemedia-cloudinary',
                'ngremotemedia-cloudinary-search_count',
            ]);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            50,
            $this->taggableCachedGateway->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::searchCount
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testSearchCountNonCachedNonTaggable(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-search_count-test|25||image,video|test_folder|tag1||created_at=desc')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $query = new Query([
            'query' => 'test',
            'types' => ['image', 'video'],
            'tags' => ['tag1'],
            'folders' => ['test_folder'],
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(50);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with(50);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            50,
            $this->nonTaggableCachedGateway->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListTagsCached(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $tags = ['tag1', 'tag2'];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-tag_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($tags);

        self::assertSame(
            $tags,
            $this->taggableCachedGateway->listTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListTagsNonCachedTaggable(): void
    {
        $cacheItem = self::createMock(ItemInterface::class);

        $tags = ['tag1', 'tag2'];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-tag_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($tags);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $cacheItem
            ->expects(self::once())
            ->method('tag')
            ->with([
                'ngremotemedia-cloudinary',
                'ngremotemedia-cloudinary-tag_list',
            ]);

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            $tags,
            $this->taggableCachedGateway->listTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::listTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::washKey
     */
    public function testListTagsNonCachedNonTaggable(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);

        $tags = ['tag1', 'tag2'];

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with('ngremotemedia-cloudinary-tag_list')
            ->willReturn($cacheItem);

        $cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        $cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($tags);

        $cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(self::CACHE_TTL);

        $this->nonTaggableCacheMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        self::assertSame(
            $tags,
            $this->nonTaggableCachedGateway->listTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getVideoThumbnail
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getImageTag
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getVideoTag
     */
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

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getDownloadLink
     */
    public function testGetDownloadLink(): void
    {
        $cloudinaryId = CloudinaryRemoteId::fromRemoteId('upload|raw|test.zip');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with($cloudinaryId)
            ->willReturn('https://cloudinary.com/test.zip');

        self::assertSame(
            'https://cloudinary.com/test.zip',
            $this->taggableCachedGateway->getDownloadLink($cloudinaryId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateResourceListCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateResourceListCache(): void
    {
        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-search',
            'ngremotemedia-cloudinary-search_count',
            'ngremotemedia-cloudinary-resource_list',
            'ngremotemedia-cloudinary-resources_count',
            'ngremotemedia-cloudinary-tag_list',
        ];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($tags);

        $this->taggableCachedGateway->invalidateResourceListCache();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateResourceListCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateResourceListCacheNonTaggable(): void
    {
        $this->nonTaggableCacheMock
            ->expects(self::never())
            ->method(self::anything());

        $this->nonTaggableCachedGateway->invalidateResourceListCache();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getItemCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateResourceCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateResourceCache(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-resource-upload-image-folder_test_image.jpg',
        ];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($tags);

        $this->taggableCachedGateway->invalidateResourceCache($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getItemCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateResourceCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateResourceCacheNonTaggable(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $this->nonTaggableCacheMock
            ->expects(self::never())
            ->method(self::anything());

        $this->nonTaggableCachedGateway->invalidateResourceCache($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateFoldersCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateFoldersCache(): void
    {
        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-folder_list',
            'ngremotemedia-cloudinary-folder_count',
        ];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($tags);

        $this->taggableCachedGateway->invalidateFoldersCache();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateFoldersCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateFoldersCacheNonTaggable(): void
    {
        $this->nonTaggableCacheMock
            ->expects(self::never())
            ->method(self::anything());

        $this->nonTaggableCachedGateway->invalidateFoldersCache();
    }

    /**
     * * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateTagsCache
     */
    public function testInvalidateTagsCache(): void
    {
        $tags = [
            'ngremotemedia-cloudinary',
            'ngremotemedia-cloudinary-tag_list',
        ];

        $this->taggableCacheMock
            ->expects(self::once())
            ->method('invalidateTags')
            ->with($tags);

        $this->taggableCachedGateway->invalidateTagsCache();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getBaseTag
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::getCacheTags
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::invalidateTagsCache
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Cache\Psr6CachedGateway::isCacheTaggable
     */
    public function testInvalidateTagsCacheNonTaggable(): void
    {
        $this->nonTaggableCacheMock
            ->expects(self::never())
            ->method(self::anything());

        $this->nonTaggableCachedGateway->invalidateTagsCache();
    }
}
