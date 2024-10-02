<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary;

use Cloudinary\Api\Exception\NotFound as CloudinaryNotFound;
use Cloudinary\Api\Upload\UploadApi;
use Doctrine\ORM\EntityManagerInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CacheableGatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
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
    private const RESOURCE_MOVE = 'move';
    private const RESOURCE_TAGS_CHANGED = 'resource_tags_changed';
    private const RESOURCE_CONTEXT_CHANGED = 'resource_context_changed';
    private const RESOURCE_RENAME = 'rename';
    private const RESOURCE_DISPLAY_NAME_CHANGED = 'resource_display_name_changed';
    private const FOLDER_CREATE = 'create_folder';
    private const FOLDER_DELETE = 'delete_folder';
    private const FOLDER_MOVE_RENAME = 'move_or_rename_asset_folder';

    public function __construct(
        private GatewayInterface $gateway,
        private ProviderInterface $provider,
        private RequestVerifierInterface $signatureVerifier,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private string $folderMode,
    ) {}

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

            case self::RESOURCE_MOVE:
                $this->handleResourceMoved($requestContent);

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

            case self::RESOURCE_DISPLAY_NAME_CHANGED:
                $this->handleDisplayNameChanged($requestContent);

                break;

            case self::FOLDER_CREATE:
            case self::FOLDER_DELETE:
            case self::FOLDER_MOVE_RENAME:
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
                ->setName($this->resolveName($requestContent))
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

    private function handleResourceMoved(array $requestContent): void
    {
        if ($this->folderMode !== CloudinaryProvider::FOLDER_MODE_DYNAMIC) {
            return;
        }

        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateResourceListCache();
            $this->gateway->invalidateFoldersCache();
        }

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

            $resource->setFolder(Folder::fromPath($resourceData['to_asset_folder']));

            if (($resourceData['display_name'] ?? null) !== null) {
                $resource->setName($resourceData['display_name']);
            }

            $this->provider->store($resource);
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

        /** @var RemoteResource $resource */
        foreach ($result as $resource) {
            try {
                $apiResource = (new UploadApi())->explicit(
                    $requestContent['to_public_id'],
                    [
                        'type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getType(),
                        'resource_type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceType(),
                    ],
                );
            } catch (CloudinaryNotFound $e) {
                continue;
            }

            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $apiResource['type'],
                $apiResource['resource_type'],
                (string) $apiResource['public_id'],
            );

            $resource
                ->setRemoteId($cloudinaryRemoteId->getRemoteId())
                ->setName($this->resolveName($requestContent))
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

    private function handleDisplayNameChanged(array $requestContent): void
    {
        if ($this->folderMode !== CloudinaryProvider::FOLDER_MODE_DYNAMIC) {
            return;
        }

        if ($this->gateway instanceof CacheableGatewayInterface) {
            $this->gateway->invalidateResourceListCache();
        }

        foreach ($requestContent['resources'] ?? [] as $resourceData) {
            $cloudinaryRemoteId = new CloudinaryRemoteId(
                $resourceData['type'],
                $resourceData['resource_type'],
                (string) $resourceData['public_id'],
            );

            if ($this->gateway instanceof CacheableGatewayInterface) {
                $this->gateway->invalidateResourceCache($cloudinaryRemoteId);
            }

            try {
                $resource = $this->provider->loadByRemoteId(
                    $cloudinaryRemoteId->getRemoteId(),
                );
            } catch (RemoteResourceNotFoundException $e) {
                continue;
            }

            $resource->setName($resourceData['new_display_name']);

            $this->provider->store($resource);
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

            $filenameFromUrl = basename((string) $publicId);

            try {
                $apiResource = (new UploadApi())->explicit(
                    CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceId(),
                    [
                        'type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getType(),
                        'resource_type' => CloudinaryRemoteId::fromRemoteId($resource->getRemoteId())->getResourceType(),
                    ],
                );

                basename($apiResource['secure_url']);
            } catch (CloudinaryNotFound $e) {
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

    private function resolveName(array $data): string
    {
        $cloudinaryRemoteId = CloudinaryRemoteId::fromCloudinaryData($data);

        return $this->folderMode === CloudinaryProvider::FOLDER_MODE_FIXED
            ? pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME)
            : $data['display_name'] ?? pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME);
    }
}
