<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Location;

use InvalidArgumentException;
use Netgen\Bundle\RemoteMediaBundle\Controller\Location\Create as CreateController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

#[CoversClass(CreateController::class)]
final class CreateTest extends TestCase
{
    private CreateController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $remoteResourceService = new RemoteResourceService($this->providerMock);
        $this->controller = new CreateController($this->providerMock, $remoteResourceService);
    }

    public function testInvalid(): void
    {
        $request = new Request(content: json_encode(['id' => null]));

        self::expectException(InvalidArgumentException::class);

        $this->controller->__invoke($request);
    }

    public function testSuccess(): void
    {
        $request = new Request(
            content: json_encode([
                'id' => '1',
                'alternateText' => 'altText',
                'caption' => 'caption',
                'tags' => [],
            ])
        );

        $remoteResource = new RemoteResource(
            remoteId: '1',
            type: 'image',
            url: 'url',
            md5: 'md5',
            altText: 'altText',
            caption: 'caption',
            tags: [],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('1')
            ->willReturn($remoteResource);

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
