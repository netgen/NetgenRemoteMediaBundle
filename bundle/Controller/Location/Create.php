<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Location;

use InvalidArgumentException;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Create extends AbstractController
{
    public function __construct(
        private ProviderInterface $provider,
        private RemoteResourceService $service,
    ) {}

    public function __invoke(Request $request): Response
    {
        $selectedImage = json_decode($request->getContent(), true);
        
        if ($selectedImage['id'] === null) {
            throw new InvalidArgumentException('No image selected.');
        }
        
        try {
            $remoteResource = $this->provider->loadByRemoteId($selectedImage['id']);
        } catch (RemoteResourceNotFoundException $e) {
            $remoteResource = $this->provider->loadFromRemote($selectedImage['id']);
        }

        $this->service->handleRemoteUpdate(
            $remoteResource,
            [
                'altText' => $selectedImage['alternateText'],
                'caption' => $selectedImage['caption'],
                'tags' => $selectedImage['tags'],
            ],
            true,
        );

        $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        $this->service->handleLocationUpdate($remoteResourceLocation, $selectedImage, true);

        return new JsonResponse([ 'locationId' => $remoteResourceLocation->getId() ]);
    }
}
