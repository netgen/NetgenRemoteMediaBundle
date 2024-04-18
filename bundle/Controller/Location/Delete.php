<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Location;

use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class Delete extends AbstractController
{
    public function __construct(
        private ProviderInterface $provider,
    ) {}

    public function __invoke(int $locationId): Response
    {
        $this->provider->removeLocation($this->provider->loadLocation($locationId));

        return new Response();
    }
}
