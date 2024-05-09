<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary;

use Cloudinary\Api\NotFound;
use Cloudinary\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CacheableGatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Core\RequestVerifierInterface;
use Netgen\RemoteMedia\Event\Cloudinary\NotificationReceivedEvent;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function basename;
use function json_decode;
use function pathinfo;

use const PATHINFO_FILENAME;

final class Notify extends AbstractController
{
    private const RESOURCE_UPLOAD = 'upload';
    private const RESOURCE_DELETE = 'delete';
    private const RESOURCE_TAGS_CHANGED = 'resource_tags_changed';
    private const RESOURCE_CONTEXT_CHANGED = 'resource_context_changed';
    private const RESOURCE_RENAME = 'rename';
    private const FOLDER_CREATE = 'create_folder';
    private const FOLDER_DELETE = 'delete_folder';

    private GatewayInterface $gateway;

    private ProviderInterface $provider;

    private RequestVerifierInterface $signatureVerifier;

    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GatewayInterface $gateway,
        ProviderInterface $provider,
        RequestVerifierInterface $signatureVerifier,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gateway = $gateway;
        $this->provider = $provider;
        $this->signatureVerifier = $signatureVerifier;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->signatureVerifier->verify($request)) {
            return $this->returnUnverified();
        }

        $event = new NotificationReceivedEvent($request);
        $this->eventDispatcher->dispatch($event, NotificationReceivedEvent::NAME);

        $requestContent = json_decode($request->getContent(), true);
        $notificationType = $requestContent['notification_type'] ?? null;

        switch ($notificationType) {
            case self::RESOURCE_UPLOAD:
                $this->handleResourceUploaded($requestContent);

                break;

            case self::RESOURCE_DELETE:
                $this->handleResourceDeleted($requestContent);

                break;

            case self::RESOURCE_TAGS_CHANGED:
                $this->handleTagsChanged($requestContent);

                break;

            case self::RESOURCE_CONTEXT_CHANGED:
                $this->handleContextChanged($requestContent);

                break;

            case self::RESOURCE_RENAME:
                $this->handleResourceRenamed($requestContent);

                break;

            case self::FOLDER_CREATE:
            case self::FOLDER_DELETE:
                $this->handleFoldersChanged();

                break;
        }

        return $this->returnSuccess();
    }

    private function returnUnverified(): Response
    {
        return new JsonResponse('Signature did not pass data verification!', Response::HTTP_BAD_REQUEST);
    }

    private function returnSuccess(): Response
    {
        return new JsonResponse('Notification handled.');
    }

    private function handleResourceUploaded(array $requestContent): void
    {
        $cloudinaryRemoteId = new CloudinaryRemoteId(
            $requestContent['type'],
            $requestContent['resource_type'],
            (string) $requestContent['public_id'],
        );

        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateResourceListCache();
            $this->gateway->invalidateTagsCache();
            $this->gateway->invalidateFoldersCache();

            $this->gateway->invalidateResourceCache($cloudinaryRemoteId);
        }

        try {
            $resource = $this->provider->loadByRemoteId($cloudinaryRemoteId->getRemoteId());

            $resource
                ->setUrl($this->gateway->getDownloadLink($cloudinaryRemoteId))
                ->setName(pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME))
                ->setVersion((string) $requestContent['version'])
                ->setSize($requestContent['bytes'])
                ->setTags($requestContent['tags']);

            $this->provider->store($resource);
        } catch (RemoteResourceNotFoundException $e) {
        }
    }

    private function handleResourceDeleted(array $requestContent): void
    {
        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateResourceListCache();
            $this->gateway->invalidateTagsCache();
            $this->gateway->invalidateFoldersCache();
        }

        foreach ($requestContent['resources'] ?? [] as $resourceData) {
            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $resourceData['type'],
                $resourceData['resource_type'],
                (string) $resourceData['public_id'],
            );

            $this->gateway->invalidateResourceCache($cloudinaryRemoteId);

            try {
                $resource = $this->provider->loadByRemoteId($cloudinaryRemoteId->getRemoteId());
                $this->provider->remove($resource);
            } catch (RemoteResourceNotFoundException $e) {
                continue;
            }
        }
    }

    /**
     * This method is a bit hacky due to inconsistent Cloudinary API response.
     */
    private function handleResourceRenamed(array $requestContent): void
    {
        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateResourceListCache();
            $this->gateway->invalidateFoldersCache();
        }

        $result = $this->entityManager
            ->getRepository(RemoteResource::class)
            ->createQueryBuilder('r')
            ->where('r.remoteId LIKE :publicId')
            ->setParameter('publicId', '%' . $requestContent['from_public_id'])
            ->getQuery()
            ->getResult();

        /** @var \Netgen\RemoteMedia\API\Values\RemoteResource $resource */
        foreach ($result as $resource) {
            try {
                $apiResource = Uploader::explicit(
                    $requestContent['to_public_id'],
                    [
                        'type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getType(),
                        'resource_type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceType(),
                    ],
                );
            } catch (NotFound $e) {
                continue;
            }

            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $apiResource['type'],
                $apiResource['resource_type'],
                (string) $apiResource['public_id'],
            );

            $resource
                ->setRemoteId($cloudinaryRemoteId->getRemoteId())
                ->setName(pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME))
                ->setUrl($this->gateway->getDownloadLink($cloudinaryRemoteId))
                ->setFolder($cloudinaryRemoteId->getFolder());

            $this->provider->store($resource);

            if ($this->gateway instanceof CacheableGatewayInterface) {
                $this->gateway->invalidateResourceCache(
                    new CloudinaryRemoteId(
                        $apiResource['type'],
                        $apiResource['resource_type'],
                        (string) $requestContent['from_public_id'],
                    ),
                );
            }
        }
    }

    private function handleTagsChanged(array $requestContent): void
    {
        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateTagsCache();
        }

        foreach ($requestContent['resources'] ?? [] as $resourceData) {
            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $resourceData['type'],
                $resourceData['resource_type'],
                (string) $resourceData['public_id'],
            );

            $this->gateway->invalidateResourceCache($cloudinaryRemoteId);

            try {
                $resource = $this->provider->loadByRemoteId(
                    $cloudinaryRemoteId->getRemoteId(),
                );
            } catch (RemoteResourceNotFoundException $e) {
                continue;
            }

            foreach ($resourceData['added'] ?? [] as $tag) {
                $resource->addTag($tag);
            }

            foreach ($resourceData['removed'] ?? [] as $tag) {
                $resource->removeTag($tag);
            }

            foreach ($resourceData['updated'] ?? [] as $tag) {
                $resource->addTag($tag);
            }

            $this->provider->store($resource);
        }
    }

    private function handleContextChanged(array $requestContent): void
    {
        foreach ($requestContent['resources'] ?? [] as $publicId => $resourceData) {
            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $resourceData['type'],
                $resourceData['resource_type'],
                (string) $publicId,
            );

            $this->gateway->invalidateResourceCache($cloudinaryRemoteId);

            try {
                $resource = $this->provider->loadByRemoteId($cloudinaryRemoteId->getRemoteId());
            } catch (RemoteResourceNotFoundException $e) {
                continue;
            }

            $filenameFromUrl = basename($publicId);

            try {
                $apiResource = Uploader::explicit(
                    CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceId(),
                    [
                        'type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getType(),
                        'resource_type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceType(),
                    ],
                );

                basename($apiResource['secure_url']);
            } catch (NotFound $e) {
                continue;
            }

            foreach ($resourceData['added'] ?? [] as $value) {
                switch ($value['name']) {
                    case 'alt':
                        $resource->setAltText($value['value']);

                        break;

                    case 'caption':
                        $resource->setCaption($value['value']);

                        break;

                    case 'original_filename':
                        $resource->setOriginalFilename($value['value'] ?? $filenameFromUrl);

                        break;

                    default:
                        $resource->addContextProperty($value['name'], $value['value']);
                }
            }

            foreach ($resourceData['removed'] ?? [] as $value) {
                switch ($value['name']) {
                    case 'alt':
                        $resource->setAltText(null);

                        break;

                    case 'caption':
                        $resource->setCaption(null);

                        break;

                    case 'original_filename':
                        $resource->setOriginalFilename($filenameFromUrl);

                        break;

                    default:
                        $resource->removeContextProperty($value['name']);
                }
            }

            foreach ($resourceData['updated'] ?? [] as $value) {
                switch ($value['name']) {
                    case 'alt':
                        $resource->setAltText($value['value']);

                        break;

                    case 'caption':
                        $resource->setCaption($value['value']);

                        break;

                    case 'original_filename':
                        $resource->setOriginalFilename($value['value'] ?? $filenameFromUrl);

                        break;

                    default:
                        $resource->addContextProperty($value['name'], $value['value']);
                }
            }

            $this->provider->store($resource);
        }
    }

    private function handleFoldersChanged(): void
    {
        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateFoldersCache();
        }
    }
}
