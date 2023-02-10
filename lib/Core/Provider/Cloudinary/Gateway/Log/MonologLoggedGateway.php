<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway\Log;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class MonologLoggedGateway implements GatewayInterface
{
    private GatewayInterface $gateway;

    private LoggerInterface $logger;

    public function __construct(GatewayInterface $gateway, ?LoggerInterface $logger)
    {
        $this->gateway = $gateway;
        $this->logger = $logger ?? new NullLogger();
    }

    public function usage(): StatusData
    {
        $this->logger->info('[API][LIMITED] usage() -> Cloudinary\Api::usage()');

        return $this->gateway->usage();
    }

    public function countResources(): int
    {
        $this->logger->info('[API][LIMITED] countResources() -> Cloudinary\Api::usage()');

        return $this->gateway->countResources();
    }

    public function countResourcesInFolder(string $folder): int
    {
        $this->logger->info("[API][LIMITED] countResourcesInFolder(\"{$folder}\") -> Cloudinary\\Search::execute(\"folder:{$folder}/*\")");

        return $this->gateway->countResourcesInFolder($folder);
    }

    public function listFolders(): array
    {
        $this->logger->info('[API][LIMITED] listFolders() -> Cloudinary\Api::root_folders()');

        return $this->gateway->listFolders();
    }

    public function listSubFolders(string $parentFolder): array
    {
        $this->logger->info("[API][LIMITED] listSubFolders(\"{$parentFolder}\") -> Cloudinary\\Api::subfolders(\"{$parentFolder}\")");

        return $this->gateway->listSubFolders($parentFolder);
    }

    public function createFolder(string $path): void
    {
        $this->logger->info("[API][LIMITED] createFolder(\"{$path}\") -> Cloudinary\\Api::create_folder(\"{$path}\")");

        $this->gateway->createFolder($path);
    }

    public function get(CloudinaryRemoteId $remoteId): RemoteResource
    {
        $this->logger->info("[API][LIMITED] get(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Api::resource(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->get($remoteId);
    }

    public function upload(string $fileUri, array $options): RemoteResource
    {
        $this->logger->info("[API][FREE] upload(\"{$fileUri}\") -> Cloudinary\\Uploader::upload(\"{$fileUri}\")");

        return $this->gateway->upload($fileUri, $options);
    }

    public function update(CloudinaryRemoteId $remoteId, array $options): void
    {
        $this->logger->info("[API][FREE] update(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Api::update(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->update($remoteId, $options);
    }

    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void
    {
        $this->logger->info("[API][FREE] removeAllTagsFromResource(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Api::remove_all_tags(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->removeAllTagsFromResource($remoteId);
    }

    public function delete(CloudinaryRemoteId $remoteId): void
    {
        $this->logger->info("[API][FREE] delete(\"{$remoteId->getRemoteId()}\") -> Cloudinary\\Uploader::destroy(\"{$remoteId->getRemoteId()}\")");

        $this->gateway->delete($remoteId);
    }

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token, array $transformations = []): string
    {
        $this->logger->info("[INTERNAL][FREE] getAuthenticatedUrl(\"{$remoteId->getRemoteId()}\") -> cloudinary_url_internal(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getAuthenticatedUrl($remoteId, $token, $transformations);
    }

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations): string
    {
        $this->logger->info("[INTERNAL][FREE] getVariationUrl(\"{$remoteId->getRemoteId()}\") -> cloudinary_url_internal(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getVariationUrl($remoteId, $transformations);
    }

    public function search(Query $query): Result
    {
        $this->logger->info("[API][LIMITED] search(\"{$query}\") -> Cloudinary\\Search::execute(\"{$query}\")");

        return $this->gateway->search($query);
    }

    public function searchCount(Query $query): int
    {
        $this->logger->info("[API][LIMITED] searchCount(\"{$query}\") -> Cloudinary\\Search::execute(\"{$query}\")");

        return $this->gateway->searchCount($query);
    }

    public function listTags(): array
    {
        $this->logger->info('[API][LIMITED] listTags() -> Cloudinary\Api::tags()');

        return $this->gateway->listTags();
    }

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $this->logger->info("[INTERNAL][FREE] getVideoThumbnail(\"{$remoteId->getRemoteId()}\") -> cl_video_thumbnail_path(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getVideoThumbnail($remoteId, $options);
    }

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $this->logger->info("[INTERNAL][FREE] getImageTag(\"{$remoteId->getRemoteId()}\") -> cl_image_tag(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getImageTag($remoteId, $options);
    }

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $this->logger->info("[INTERNAL][FREE] getVideoTag(\"{$remoteId->getRemoteId()}\") -> cl_video_tag(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getVideoTag($remoteId, $options);
    }

    public function getDownloadLink(CloudinaryRemoteId $remoteId): string
    {
        $this->logger->info("[INTERNAL][FREE] getDownloadLink(\"{$remoteId->getRemoteId()}\") -> Cloudinary::cloudinary_url(\"{$remoteId->getRemoteId()}\")");

        return $this->gateway->getDownloadLink($remoteId);
    }
}
