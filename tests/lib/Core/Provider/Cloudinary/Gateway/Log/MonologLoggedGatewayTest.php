<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Gateway\Log;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

use function count;

final class MonologLoggedGatewayTest extends AbstractTest
{
    /**
     * @var \Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface
     */
    protected $gateway;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface
     */
    protected MockObject $apiGatewayMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->apiGatewayMock = self::createMock(GatewayInterface::class);
        $this->loggerMock = self::createMock(LoggerInterface::class);

        $this->gateway = new MonologLoggedGateway(
            $this->apiGatewayMock,
            $this->loggerMock,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::usage
     */
    public function testUsage(): void
    {
        $usageData = new StatusData([
            'plan' => 'Advanced',
            'limit' => 1000,
            'remaining_limit' => 990,
        ]);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with('[API][LIMITED] usage() -> Cloudinary\Api::usage()');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('usage')
            ->willReturn($usageData);

        $result = $this->gateway->usage();

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
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::isEncryptionEnabled
     */
    public function testIsEncryptionEnabled(): void
    {
        $this->apiGatewayMock
            ->expects(self::exactly(2))
            ->method('isEncryptionEnabled')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->loggerMock
            ->expects(self::exactly(2))
            ->method('info')
            ->with('[INTERNAL][FREE] isEncryptionEnabled()');

        self::assertTrue($this->gateway->isEncryptionEnabled());
        self::assertFalse($this->gateway->isEncryptionEnabled());
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::countResources
     */
    public function testCountResources(): void
    {
        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResources')
            ->willReturn(500);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with('[API][LIMITED] countResources() -> Cloudinary\Api::usage()');

        self::assertSame(
            500,
            $this->gateway->countResources(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::countResourcesInFolder
     */
    public function testCountResourcesInFolder(): void
    {
        $folder = 'test/subtest';

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('countResourcesInFolder')
            ->with($folder)
            ->willReturn(200);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] countResourcesInFolder(\"{$folder}\") -> Cloudinary\\Search::execute(\"folder:{$folder}/*\")");

        self::assertSame(
            200,
            $this->gateway->countResourcesInFolder($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::listFolders
     */
    public function testListFolders(): void
    {
        $folders = [
            'test',
            'test/subfolder1',
            'test/subfolder2',
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with('[API][LIMITED] listFolders() -> Cloudinary\Api::root_folders()');

        self::assertSame(
            $folders,
            $this->gateway->listFolders(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::listSubFolders
     */
    public function testListSubFolders(): void
    {
        $folder = 'test';

        $subFolders = [
            'subfolder1',
            'subfolder2',
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listSubFolders')
            ->with($folder)
            ->willReturn($subFolders);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] listSubFolders(\"{$folder}\") -> Cloudinary\\Api::subfolders(\"{$folder}\")");

        self::assertSame(
            $subFolders,
            $this->gateway->listSubFolders($folder),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::createFolder
     */
    public function testCreateFolder(): void
    {
        $path = 'test/subfolder/newfolder';

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('createFolder')
            ->with($path);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] createFolder(\"{$path}\") -> Cloudinary\\Api::create_folder(\"{$path}\")");

        $this->gateway->createFolder($path);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::get
     */
    public function testGet(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|folder/test_image.jpg');

        $remoteResource = new RemoteResource([
            'remoteId' => $remoteId->getRemoteId(),
            'type' => RemoteResource::TYPE_IMAGE,
            'url' => 'https://res.cloudinary.com/demo/image/upload/folder/test_image.jpg',
            'name' => 'test_image.jpg',
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
            'metadata' => [
                'format' => 'jpg',
            ],
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('get')
            ->with($remoteId)
            ->willReturn($remoteResource);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] get(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Api::resource(\"{$remoteId->getRemoteId()}\")");

        self::assertRemoteResourceSame(
            $remoteResource,
            $this->gateway->get($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::upload
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
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('upload')
            ->with($fileUri, $options)
            ->willReturn($resource);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][FREE] upload(\"{$fileUri}\") -> Cloudinary\\Uploader::upload(\"{$fileUri}\")");

        self::assertRemoteResourceSame(
            $resource,
            $this->gateway->upload($fileUri, $options),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::update
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

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][FREE] update(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Uploader::explicit(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->update($remoteId, $options);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::removeAllTagsFromResource
     */
    public function testRemoveAllTagsFromResource(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('removeAllTagsFromResource')
            ->with($remoteId);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][FREE] removeAllTagsFromResource(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Api::remove_all_tags(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->removeAllTagsFromResource($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::delete
     */
    public function testDelete(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|test_image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('delete')
            ->with($remoteId);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][FREE] delete(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Uploader::destroy(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->delete($remoteId);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getAuthenticatedUrl
     */
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

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with('[INTERNAL][FREE] getAuthenticatedUrl("upload|image|folder/test_image.jpg") -> cloudinary_url_internal("upload|image|folder/test_image.jpg")');

        self::assertSame(
            $url,
            $this->gateway->getAuthenticatedUrl($remoteId, $token),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getVariationUrl
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
            ->willReturn('https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/folder/test_image.jpg');

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[INTERNAL][FREE] getVariationUrl(\"{$remoteId->getRemoteId()}\") -> cloudinary_url_internal(\"{$remoteId->getRemoteId()}\")");

        self::assertSame(
            'https://res.cloudinary.com/testcloud/image/upload/c_crop,h_200,w_300,x_50,y_50/folder/test_image.jpg',
            $this->gateway->getVariationUrl($remoteId, $transformations),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::search
     */
    public function testSearch(): void
    {
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
            'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
        ]);

        $searchResult = new Result(200, '123', [$resource]);

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] search(\"{$query}\") -> Cloudinary\\Search::execute(\"{$query}\")");

        self::assertSearchResultSame(
            $searchResult,
            $this->gateway->search($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::searchCount
     */
    public function testSearchCount(): void
    {
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

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[API][LIMITED] searchCount(\"{$query}\") -> Cloudinary\\Search::execute(\"{$query}\")");

        self::assertSame(
            50,
            $this->gateway->searchCount($query),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::listTags
     */
    public function testListTags(): void
    {
        $tags = ['tag1', 'tag2'];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with('[API][LIMITED] listTags() -> Cloudinary\Api::tags()');

        self::assertSame(
            $tags,
            $this->gateway->listTags(),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getVideoThumbnail
     */
    public function testGetVideoThumbnail(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|video|example.mp4');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with($remoteId)
            ->willReturn('video_thumbnail.jpg');

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[INTERNAL][FREE] getVideoThumbnail(\"{$remoteId->getRemoteId()}\") -> cl_video_thumbnail_path(\"{$remoteId->getRemoteId()}\")");

        self::assertSame(
            'video_thumbnail.jpg',
            $this->gateway->getVideoThumbnail($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getImageTag
     */
    public function testGetImageTag(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|image|image.jpg');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getImageTag')
            ->with($remoteId)
            ->willReturn('<img src="image.jpg"/>');

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[INTERNAL][FREE] getImageTag(\"{$remoteId->getRemoteId()}\") -> cl_image_tag(\"{$remoteId->getRemoteId()}\")");

        self::assertSame(
            '<img src="image.jpg"/>',
            $this->gateway->getImageTag($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getVideoTag
     */
    public function testGetVideoTag(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|video|example.mp4');

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getVideoTag')
            ->with($remoteId)
            ->willReturn('<video src="example.mp4"/>');

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[INTERNAL][FREE] getVideoTag(\"{$remoteId->getRemoteId()}\") -> cl_video_tag(\"{$remoteId->getRemoteId()}\")");

        self::assertSame(
            '<video src="example.mp4"/>',
            $this->gateway->getVideoTag($remoteId),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log\MonologLoggedGateway::getDownloadLink
     */
    public function testGetDownloadLink(): void
    {
        $remoteId = CloudinaryRemoteId::fromRemoteId('upload|raw|test.zip');

        $options = [
            'transformations' => [],
        ];

        $this->apiGatewayMock
            ->expects(self::once())
            ->method('getDownloadLink')
            ->with($remoteId, $options)
            ->willReturn('https://cloudinary.com/test.zip');

        $this->loggerMock
            ->expects(self::once())
            ->method('info')
            ->with("[INTERNAL][FREE] getDownloadLink(\"{$remoteId->getRemoteId()}\") -> Cloudinary::cloudinary_url(\"{$remoteId->getRemoteId()}\")");

        self::assertSame(
            'https://cloudinary.com/test.zip',
            $this->gateway->getDownloadLink($remoteId, $options),
        );
    }
}
