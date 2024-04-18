<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Location;

use InvalidArgumentException;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Update extends AbstractController
{
    public function __construct(
        private ProviderInterface $provider,
        private RemoteResourceService $service,
    ) {}

    public function __invoke(int $locationId, Request $request): Response
    {
        $selectedImage = json_decode($request->getContent(), true);
        
        if ($selectedImage['id'] === null) {
            throw new InvalidArgumentException('No selected image data.');
        }

        $remoteResourceLocation = $this->provider->loadLocation($locationId);
        if ($remoteResourceLocation->getRemoteResource()->getRemoteId() !== $selectedImage['id']) {
            throw new InvalidArgumentException('Trying to update location with new resource. Instead, delete this location and create a new one.');
        }

        $this->service->handleRemoteUpdate(
            $remoteResourceLocation->getRemoteResource(),
            [
                'altText' => $selectedImage['alternateText'],
                'caption' => $selectedImage['caption'],
                'tags' => $selectedImage['tags'],
            ],
            true,
        );

        $this->service->handleLocationUpdate($remoteResourceLocation, $selectedImage, true);

        return new Response();
    }
}
