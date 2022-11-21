<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Resource;

use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload as UploadController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

final class UploadTest extends TestCase
{
    private UploadController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->controller = new UploadController($this->providerMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload::formatResource
     */
    public function test(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::exactly(2))
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
            'size' => 123,
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
}
