<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use function gettype;
use function in_array;
use function is_array;

final class RemoteResourceFactory implements RemoteResourceFactoryInterface
{
    private ResourceTypeConverter $resourceTypeConverter;

    public function __construct(ResourceTypeConverter $resourceTypeConverter)
    {
        $this->resourceTypeConverter = $resourceTypeConverter;
    }

    public function create($data): RemoteResource
    {
        $this->validateData($data);

        return new RemoteResource([
            'remoteId' => CloudinaryRemoteId::fromCloudinaryData($data)->getRemoteId(),
            'type' => $this->resolveResourceType($data),
            'url' => $data['secure_url'] ?? $data['url'],
            'size' => $data['bytes'] ?? 0,
            'altText' => $this->resolveAltText($data),
            'caption' => $data['context']['custom']['caption'] ?? null,
            'tags' => $data['tags'] ?? [],
            'metaData' => $this->resolveMetaData($data),
        ]);
    }

    /**
     * @param mixed $data
     *
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    private function validateData($data): void
    {
        if (!is_array($data)) {
            throw new InvalidDataException('CloudinaryRemoteResourceFactory requires an array, "' . gettype($data) . '" provided.');
        }

        if (($data['public_id'] ?? null) === null) {
            throw new InvalidDataException('Missing required "public_id" property!');
        }
    }

    private function resolveResourceType(array $data): string
    {
        $type = $data['resource_type'] ?? RemoteResource::TYPE_OTHER;
        $format = $data['format'] ?? null;

        return $this->resourceTypeConverter->fromCloudinaryData($type, $format);
    }

    private function resolveAltText(array $data): ?string
    {
        if (($data['context']['custom']['alt_text'] ?? null) !== null) {
            return $data['context']['custom']['alt_text'];
        }

        return $data['context']['alt_text'] ?? null;
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
            'etag',
            'overwritten',
        ];

        $metaData = $data;
        foreach ($metaData as $key => $value) {
            if (!in_array($key, $supportedMetaData, true)) {
                unset($metaData[$key]);
            }
        }

        return $metaData;
    }
}
