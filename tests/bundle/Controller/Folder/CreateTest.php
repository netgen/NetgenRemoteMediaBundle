<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Folder;

use Netgen\Bundle\RemoteMediaBundle\Controller\Folder\Create as CreateController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(CreateController::class)]
final class CreateTest extends TestCase
{
    private CreateController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->controller = new CreateController($this->providerMock);
    }

    public function testInvalid(): void
    {
        $request = new Request();

        self::expectException(BadRequestException::class);

        $this->controller->__invoke($request);
    }

    public function test(): void
    {
        $request = new Request();
        $request->request->add([
            'folder' => 'new',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('createFolder')
            ->with('new')
            ->willReturn(Folder::fromPath('new'));

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    public function testWithParent(): void
    {
        $request = new Request();
        $request->request->add([
            'parent' => 'media',
            'folder' => 'new',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('createFolder')
            ->with('new', Folder::fromPath('media'))
            ->willReturn(Folder::fromPath('media/new'));

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }
}
