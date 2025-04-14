<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Location;

use InvalidArgumentException;
use Netgen\Bundle\RemoteMediaBundle\Controller\Location\Update as UpdateController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

#[CoversClass(UpdateController::class)]
final class UpdateTest extends TestCase
{
    private UpdateController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $remoteResourceService = new RemoteResourceService($this->providerMock);
        $this->controller = new UpdateController($this->providerMock, $remoteResourceService);
    }

    public function testInvalid(): void
    {
        $request = new Request(content: json_encode(['id' => null]));

        self::expectException(InvalidArgumentException::class);

        $this->controller->__invoke(1, $request);
    }

    public function testWrongRemoteResource(): void
    {
        $request = new Request(content: json_encode(['id' => '2']));

        $location = new RemoteResourceLocation($this->getRemoteResource(), id: 1);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(1)
            ->willReturn($location);

        self::expectException(InvalidArgumentException::class);

        $this->controller->__invoke($location->getId(), $request);
    }

    public function testSuccess(): void
    {
        $request = new Request(
            content: json_encode([
                'id' => '1',
                'alternateText' => 'altText',
                'caption' => 'caption',
                'tags' => [],
            ]),
        );

        $location = new RemoteResourceLocation($this->getRemoteResource(), id: 1);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(1)
            ->willReturn($location);

        $response = $this->controller->__invoke($location->getId(), $request);

        self::assertInstanceOf(
            Response::class,
            $response,
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    private function getRemoteResource(): RemoteResource
    {
        return new RemoteResource(
            remoteId: '1',
            type: 'image',
            url: 'url',
            md5: 'md5',
            altText: 'altText',
            caption: 'caption',
            tags: [],
        );
    }
}
