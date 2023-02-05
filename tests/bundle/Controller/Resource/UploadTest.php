<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Resource;

use InvalidArgumentException;
use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload as UploadController;
use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

final class UploadTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\Factory\FileHash */
    protected MockObject $fileHashFactoryMock;
    private UploadController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->fileHashFactoryMock = $this->createMock(FileHashFactoryInterface::class);

        $this->controller = new UploadController($this->providerMock, $this->fileHashFactoryMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::formatResource
     */
    public function testUpload(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::exactly(3))
            ->method('getRealPath')
            ->willReturn('/var/www/project/media/images/sample_image.jpg');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalName')
            ->willReturn('sample_image');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $request->files->add([
            'file' => $uploadedFileMock,
        ]);

        $this->fileHashFactoryMock
            ->expects(self::once())
            ->method('createHash')
            ->with('/var/www/project/media/images/sample_image.jpg')
            ->willReturn('a522f23sf81aa0afd03387c37e2b6eax');

        $fileStruct = FileStruct::fromUploadedFile($uploadedFileMock);

        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            Folder::fromPath('media/image'),
            $request->request->get('filename'),
            $request->request->getBoolean('overwrite', false),
            $request->request->getBoolean('invalidate', false),
        );

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'folder' => Folder::fromPath('media/image'),
            'size' => 123,
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willReturn($resource);

        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('buildRawVariation')
            ->with($resource, [$transformation])
            ->willReturn($variation);

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'altText' => null,
        ]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::formatResource
     */
    public function testUploadExistingFile(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::exactly(3))
            ->method('getRealPath')
            ->willReturn('/var/www/project/media/images/sample_image.jpg');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalName')
            ->willReturn('sample_image');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $request->files->add([
            'file' => $uploadedFileMock,
        ]);

        $this->fileHashFactoryMock
            ->expects(self::once())
            ->method('createHash')
            ->with('/var/www/project/media/images/sample_image.jpg')
            ->willReturn('a522f23sf81aa0afd03387c37e2b6eax');

        $fileStruct = FileStruct::fromUploadedFile($uploadedFileMock);

        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            Folder::fromPath('media/image'),
            $request->request->get('filename'),
            $request->request->getBoolean('overwrite', false),
            $request->request->getBoolean('invalidate', false),
        );

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'folder' => Folder::fromPath('media/image'),
            'size' => 123,
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willThrowException(new RemoteResourceExistsException($resource));

        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('buildRawVariation')
            ->with($resource, [$transformation])
            ->willReturn($variation);

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'altText' => null,
        ]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::formatResource
     */
    public function testUploadExistingFileName(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::exactly(3))
            ->method('getRealPath')
            ->willReturn('/var/www/project/media/images/sample_image.jpg');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalName')
            ->willReturn('sample_image');

        $uploadedFileMock
            ->expects(self::exactly(2))
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $request->files->add([
            'file' => $uploadedFileMock,
        ]);

        $this->fileHashFactoryMock
            ->expects(self::once())
            ->method('createHash')
            ->with('/var/www/project/media/images/sample_image.jpg')
            ->willReturn('c572a23sf31aa0afd03387c37e2b6q3g');

        $fileStruct = FileStruct::fromUploadedFile($uploadedFileMock);

        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            Folder::fromPath('media/image'),
            $request->request->get('filename'),
            $request->request->getBoolean('overwrite', false),
            $request->request->getBoolean('invalidate', false),
        );

        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'folder' => Folder::fromPath('media/image'),
            'size' => 123,
            'md5' => 'a522f23sf81aa0afd03387c37e2b6eax',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willThrowException(new RemoteResourceExistsException($resource));

        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $variation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::once())
            ->method('buildRawVariation')
            ->with($resource, [$transformation])
            ->willReturn($variation);

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'altText' => null,
        ]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_CONFLICT,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::formatResource
     */
    public function testUploadWithoutFile(): void
    {
        $request = new Request();

        self::expectException(InvalidArgumentException::class);

        $this->controller->__invoke($request);
    }
}