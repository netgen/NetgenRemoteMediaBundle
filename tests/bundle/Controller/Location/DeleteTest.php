<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Location;

use Netgen\Bundle\RemoteMediaBundle\Controller\Location\Delete as DeleteController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(DeleteController::class)]
final class DeleteTest extends TestCase
{
    private DeleteController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->controller = new DeleteController($this->providerMock);
    }

    public function testWrongLocation(): void
    {
        $id = 1;

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with($id)
            ->willThrowException(new RemoteResourceLocationNotFoundException($id));

        self::expectException(RemoteResourceLocationNotFoundException::class);

        $this->controller->__invoke(1);
    }

    public function testCorrectLocation(): void
    {
        $response = $this->controller->__invoke(1);

        self::assertInstanceOf(
            Response::class,
            $response,
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }
}
