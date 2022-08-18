<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Cloudinary\Api\Response;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource as RemoteResourceValue;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;

use function gettype;
use function in_array;

final class RemoteResource implements RemoteResourceFactoryInterface
{
    private ResourceTypeConverter $resourceTypeConverter;

    public function __construct(ResourceTypeConverter $resourceTypeConverter)
    {
        $this->resourceTypeConverter = $resourceTypeConverter;
    }

    public function create($data): RemoteResourceValue
    {
        $this->validateData($data);

        return new RemoteResourceValue([
            'remoteId' => CloudinaryRemoteId::fromCloudinaryResponse($data)->getRemoteId(),
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
        if (!$data instanceof Response) {
            throw new InvalidDataException('CloudinaryRemoteResourceFactory requires "Cloudinary\Api\Response" as data, "' . gettype($data) . '" provided.');
        }

        if (($data['public_id'] ?? null) === null) {
            throw new InvalidDataException('Missing required "public_id" property!');
        }
    }

    private function resolveResourceType(Response $data): string
    {
        $type = $data['resource_type'] ?? RemoteResourceValue::TYPE_OTHER;
        $format = $data['format'] ?? null;

        return $this->resourceTypeConverter->fromCloudinaryData($type, $format);
    }

    private function resolveAltText(Response $data): ?string
    {
        if (($data['context']['custom']['alt_text'] ?? null) !== null) {
            return $data['context']['custom']['alt_text'];
        }

        return $data['context']['alt_text'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMetaData(Response $data): array
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

        $metaData = (array) $data;
        foreach ($metaData as $key => $value) {
            if (!in_array($key, $supportedMetaData, true)) {
                unset($metaData[$key]);
            }
        }

        return $metaData;
    }
}
