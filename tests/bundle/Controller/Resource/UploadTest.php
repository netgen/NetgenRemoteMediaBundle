<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Resource;

use InvalidArgumentException;
use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\AbstractController;
use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Upload as UploadController;
use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\FileStruct;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function json_encode;

#[CoversClass(UploadController::class)]
#[CoversClass(AbstractController::class)]
final class UploadTest extends TestCase
{
    protected FileHashFactoryInterface|MockObject $fileHashFactoryMock;
    private MockObject|ProviderInterface $providerMock;

    private MockObject|TranslatorInterface $translatorMock;

    private UploadController $controller;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->fileHashFactoryMock = $this->createMock(FileHashFactoryInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $this->controller = new UploadController(
            $this->providerMock,
            $this->fileHashFactoryMock,
            $this->translatorMock,
        );
    }

    public function testUpload(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::once())
            ->method('isFile')
            ->willReturn(true);

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
            'public',
            $request->request->get('filename'),
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|media/image/sample_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'sample_image.jpg',
            folder: Folder::fromPath('media/image'),
            size: 123,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willReturn($resource);

        $browseVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
        );

        $previewVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_800_600/upload/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (
                    RemoteResourceLocation $location,
                    string $variationGroup,
                    string $variationName
                ) => match ($location->getRemoteResource()->getRemoteId()) {
                    'upload|image|media/image/sample_image.jpg' => $variationName === 'browse' ? $browseVariation : $previewVariation,
                    default => null,
                },
            );

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'visibility' => 'public',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'originalFilename' => null,
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'altText' => null,
            'caption' => null,
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

    public function testUploadProtectedWithContext(): void
    {
        $uploadContext = [
            'type' => 'product_image',
            'test' => 'test_value',
        ];

        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
            'visibility' => 'protected',
            'upload_context' => $uploadContext,
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::once())
            ->method('isFile')
            ->willReturn(true);

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
            'protected',
            $request->request->get('filename'),
            false,
            false,
            null,
            null,
            [],
            $uploadContext,
        );

        $resource = new RemoteResource(
            remoteId: 'authenticated|image|media/image/sample_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/authenticated/image/media/image/sample_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'sample_image.jpg',
            visibility: 'protected',
            folder: Folder::fromPath('media/image'),
            size: 123,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willReturn($resource);

        $authToken = AuthToken::fromDuration(600);

        $authenticatedResource = new AuthenticatedRemoteResource(
            remoteResource: $resource,
            url: 'https://cloudinary.com/test/authenticated/image/media/image/sample_image.jpg?token=c2f306cbe596eafd3e2eaf4d3a820832',
            token: $authToken,
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('authenticateRemoteResource')
            ->willReturn($authenticatedResource);

        $browseVariation = new RemoteResourceVariation(
            $authenticatedResource,
            'https://cloudinary.com/test/c_fit_160_120/authenticated/image/media/image/sample_image.jpg',
        );

        $previewVariation = new RemoteResourceVariation(
            $authenticatedResource,
            'https://cloudinary.com/test/c_fit_800_600/authenticated/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (
                    RemoteResourceLocation $location,
                    string $variationGroup,
                    string $variationName
                ) => match ($location->getRemoteResource()->getRemoteId()) {
                    'authenticated|image|media/image/sample_image.jpg' => $variationName === 'browse_protected' ? $browseVariation : $previewVariation,
                    default => null,
                },
            );

        $expectedResponseContent = json_encode([
            'remoteId' => 'authenticated|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'visibility' => 'protected',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'originalFilename' => null,
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/authenticated/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/authenticated/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/authenticated/image/media/image/sample_image.jpg',
            'altText' => null,
            'caption' => null,
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

    public function testUploadInvalidVisibility(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
            'visibility' => 'test',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $request->files->add([
            'file' => $uploadedFileMock,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('getSupportedVisibilities')
            ->willReturn(['public', 'protected']);

        $this->translatorMock
            ->expects(self::once())
            ->method('trans')
            ->with(
                'ngrm.edit.vue.upload.error.invalid_visibility',
                [
                    '%visibility%' => 'test',
                    '%supported_visibilities%' => 'public", "protected',
                ],
                'ngremotemedia',
            )
            ->willReturn('Invalid visibility option "test", supported options: "public", "protected".');

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid visibility option "test", supported options: "public", "protected".');

        $this->controller->__invoke($request);
    }

    public function testUploadExistingFile(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'null',
            'upload_context' => 4,
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::once())
            ->method('isFile')
            ->willReturn(true);

        $uploadedFileMock
            ->expects(self::exactly(3))
            ->method('getRealPath')
            ->willReturn('/var/www/project/sample_image.jpg');

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
            ->with('/var/www/project/sample_image.jpg')
            ->willReturn('a522f23sf81aa0afd03387c37e2b6eax');

        $fileStruct = FileStruct::fromUploadedFile($uploadedFileMock);

        $resourceStruct = new ResourceStruct(
            $fileStruct,
            'auto',
            null,
            'public',
            $request->request->get('filename'),
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|sample_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/sample_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'sample_image.jpg',
            folder: null,
            size: 123,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willThrowException(new RemoteResourceExistsException($resource));

        $browseVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/sample_image.jpg',
        );

        $previewVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_800_600/upload/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (
                    RemoteResourceLocation $location,
                    string $variationGroup,
                    string $variationName
                ) => match ($location->getRemoteResource()->getRemoteId()) {
                    'upload|image|sample_image.jpg' => $variationName === 'browse' ? $browseVariation : $previewVariation,
                    default => null,
                },
            );

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|sample_image.jpg',
            'folder' => null,
            'tags' => [],
            'type' => 'image',
            'visibility' => 'public',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'originalFilename' => null,
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/sample_image.jpg',
            'altText' => null,
            'caption' => null,
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

    public function testUploadInvalidFile(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'null',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::once())
            ->method('isFile')
            ->willReturn(false);

        $request->files->add([
            'file' => $uploadedFileMock,
        ]);

        $this->translatorMock
            ->expects(self::once())
            ->method('trans')
            ->with(
                'ngrm.edit.vue.upload.error.file_upload_failed',
                [],
                'ngremotemedia',
            )
            ->willReturn('File upload failed; the file might be too big or corrupted. Check your server file upload size settings.');

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('File upload failed; the file might be too big or corrupted. Check your server file upload size settings.');

        $response = $this->controller->__invoke($request);
    }

    public function testUploadExistingFileName(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'media/image',
            'upload_context' => 'test',
        ]);

        $uploadedFileMock = $this->createMock(UploadedFile::class);

        $uploadedFileMock
            ->expects(self::once())
            ->method('isFile')
            ->willReturn(true);

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
            'public',
            $request->request->get('filename'),
        );

        $resource = new RemoteResource(
            remoteId: 'upload|image|media/image/sample_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'sample_image.jpg',
            folder: Folder::fromPath('media/image'),
            size: 123,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('upload')
            ->with($resourceStruct)
            ->willThrowException(new RemoteResourceExistsException($resource));

        $browseVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
        );

        $previewVariation = new RemoteResourceVariation(
            $resource,
            'https://cloudinary.com/test/c_fit_800_600/upload/image/media/image/sample_image.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (
                    RemoteResourceLocation $location,
                    string $variationGroup,
                    string $variationName
                ) => match ($location->getRemoteResource()->getRemoteId()) {
                    'upload|image|media/image/sample_image.jpg' => $variationName === 'browse' ? $browseVariation : $previewVariation,
                    default => null,
                },
            );

        $expectedResponseContent = json_encode([
            'remoteId' => 'upload|image|media/image/sample_image.jpg',
            'folder' => 'media/image',
            'tags' => [],
            'type' => 'image',
            'visibility' => 'public',
            'size' => 123,
            'width' => null,
            'height' => null,
            'filename' => 'sample_image.jpg',
            'originalFilename' => null,
            'format' => null,
            'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/image/media/image/sample_image.jpg',
            'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/image/media/image/sample_image.jpg',
            'url' => 'https://cloudinary.com/test/upload/image/media/image/sample_image.jpg',
            'altText' => null,
            'caption' => null,
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

    public function testUploadWithoutFile(): void
    {
        $request = new Request();

        $this->translatorMock
            ->expects(self::once())
            ->method('trans')
            ->with(
                'ngrm.edit.vue.upload.error.missing_file',
                [],
                'ngremotemedia',
            )
            ->willReturn('Missing file to upload');

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Missing file to upload');

        $this->controller->__invoke($request);
    }
}
