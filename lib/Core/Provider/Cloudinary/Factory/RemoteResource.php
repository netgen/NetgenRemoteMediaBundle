<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource as RemoteResourceValue;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;

use function in_array;
use function pathinfo;

use const PATHINFO_FILENAME;

final class RemoteResource implements RemoteResourceFactoryInterface
{
    private ResourceTypeConverter $resourceTypeConverter;

    private VisibilityTypeConverter $visibilityTypeConverter;

    private FileHashFactoryInterface $fileHashFactory;

    public function __construct
    (
        ResourceTypeConverter $resourceTypeConverter,
        VisibilityTypeConverter $visibilityTypeConverter,
        FileHashFactoryInterface $fileHashFactory
    ) {
        $this->resourceTypeConverter = $resourceTypeConverter;
        $this->visibilityTypeConverter = $visibilityTypeConverter;
        $this->fileHashFactory = $fileHashFactory;
    }

    public function create($data): RemoteResourceValue
    {
        $this->validateData($data);

        $cloudinaryRemoteId = CloudinaryRemoteId::fromCloudinaryData($data);

        return new RemoteResourceValue([
            'remoteId' => $cloudinaryRemoteId->getRemoteId(),
            'type' => $this->resolveResourceType($data),
            'url' => $data['secure_url'] ?? $data['url'],
            'name' => pathinfo($cloudinaryRemoteId->getResourceId(), PATHINFO_FILENAME),
            'folder' => $cloudinaryRemoteId->getFolder(),
            'visibility' => $this->resolveVisibility($data),
            'size' => $data['bytes'] ?? 0,
            'altText' => $this->resolveAltText($data),
            'caption' => $data['context']['custom']['caption'] ?? null,
            'tags' => $data['tags'] ?? [],
            'md5' => $this->resolveMd5($data),
            'metadata' => $this->resolveMetaData($data),
        ]);
    }

    /**
     * @param mixed $data
     *
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    private function validateData($data): void
    {
        if (($data['public_id'] ?? null) === null) {
            throw new InvalidDataException('Missing required "public_id" property!');
        }

        if ($data['secure_url'] ?? $data['url'] ?? null) {
            return;
        }

        throw new InvalidDataException('Missing required "secure_url" or "url" property!');
    }

    private function resolveResourceType(array $data): string
    {
        $type = $data['resource_type'] ?? RemoteResourceValue::TYPE_OTHER;
        $format = $data['format'] ?? null;

        return $this->resourceTypeConverter->fromCloudinaryData($type, $format);
    }

    private function resolveVisibility(array $data): string
    {
        $type = $data['type'] ?? 'upload';

        return $this->visibilityTypeConverter->fromCloudinaryType($type);
    }

    private function resolveAltText(array $data): ?string
    {
        if (($data['context']['custom']['alt_text'] ?? null) !== null) {
            return $data['context']['custom']['alt_text'];
        }

        return $data['context']['alt_text'] ?? null;
    }

    private function resolveMd5(array $data): string
    {
        if ($data['etag'] ?? null) {
            return $data['etag'];
        }

        $url = $data['secure_url'] ?? $data['url'];

        return $this->fileHashFactory->createHash($url);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMetaData(array $data): array
    {
        $supportedMetaData = [
            'version',
            'width',
            'height',
            'format',
            'created_at',
            'signature',
            'overwritten',
        ];

        $metadata = $data;
        foreach ($metadata as $key => $value) {
            if (!in_array($key, $supportedMetaData, true)) {
                unset($metadata[$key]);
            }
        }

        return $metadata;
    }
}
