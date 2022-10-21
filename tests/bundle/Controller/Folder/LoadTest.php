<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Folder;

use Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Load as LoadController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

final class LoadTest extends TestCase
{
    private LoadController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->controller = new LoadController($this->providerMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Load::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Load::__invoke
     */
    public function test(): void
    {
        $request = new Request();

        $folders = [
            Folder::fromPath('media'),
            Folder::fromPath('media/images'),
            Folder::fromPath('media/videos'),
            Folder::fromPath('media/raw'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $expectedResponseContent = json_encode([
            [
                'id' => 'media',
                'label' => 'media',
                'children' => null,
            ],
            [
                'id' => 'media/images',
                'label' => 'images',
                'children' => null,
            ],
            [
                'id' => 'media/videos',
                'label' => 'videos',
                'children' => null,
            ],
            [
                'id' => 'media/raw',
                'label' => 'raw',
                'children' => null,
            ],
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
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Load::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Load::__invoke
     */
    public function testWithParent(): void
    {
        $request = new Request();
        $request->query->add([
            'folder' => 'media',
        ]);

        $folders = [
            Folder::fromPath('media/images'),
            Folder::fromPath('media/videos'),
            Folder::fromPath('media/raw'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->with(Folder::fromPath('media'))
            ->willReturn($folders);

        $expectedResponseContent = json_encode([
            [
                'id' => 'media/images',
                'label' => 'images',
                'children' => null,
            ],
            [
                'id' => 'media/videos',
                'label' => 'videos',
                'children' => null,
            ],
            [
                'id' => 'media/raw',
                'label' => 'raw',
                'children' => null,
            ],
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
