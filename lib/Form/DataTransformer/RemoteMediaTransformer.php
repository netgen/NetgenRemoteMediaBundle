<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Component\Form\DataTransformerInterface;

use function is_array;
use function sort;

final class RemoteMediaTransformer implements DataTransformerInterface
{
    public function __construct(
        private ProviderInterface $provider,
        private RemoteResourceService $service,
    ) {}

    public function transform($value)
    {
        if (!$value instanceof RemoteResourceLocation) {
            return null;
        }

        return [
            'locationId' => $value->getId(),
            'remoteId' => $value->getRemoteResource()->getRemoteId(),
            'type' => $value->getRemoteResource()->getType(),
            'altText' => $value->getRemoteResource()->getAltText(),
            'caption' => $value->getRemoteResource()->getCaption(),
            'tags' => $value->getRemoteResource()->getTags(),
            'cropSettings' => $this->service->resolveCropSettingsString($value),
            'source' => $value->getSource(),
            'watermarkText' => $value->getWatermarkText(),
            'altTextOverride' => $value->getAltTextOverride(),
            'captionOverride' => $value->getCaptionOverride(),
        ];
    }

    public function reverseTransform($value)
    {
        if (!is_array($value) || ($value['remoteId'] ?? null) === null || ($value['remoteId'] ?? '') === '') {
            return null;
        }

        try {
            $remoteResource = $this->provider->loadByRemoteId((string) $value['remoteId']);
        } catch (RemoteResourceNotFoundException $e) {
            try {
                $remoteResource = $this->provider->loadFromRemote((string) $value['remoteId']);
            } catch (RemoteResourceNotFoundException $e) {
                $this->service->logMissingResource((string) $value['remoteId']);

                return null;
            }
        }

        $locationId = $value['locationId'] ?? null;

        try {
            $remoteResourceLocation = $locationId !== null && $locationId !== ''
                ? $this->provider->loadLocation((int) $locationId)
                : new RemoteResourceLocation($remoteResource);
        } catch (RemoteResourceLocationNotFoundException $e) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        if ($remoteResourceLocation->getRemoteResource()->getRemoteId() !== $remoteResource->getRemoteId()) {
            $remoteResourceLocation = new RemoteResourceLocation($remoteResource);
        }

        // A resource that is gone from the remote is kept, the service logs it.
        $this->service->handleRemoteUpdate($remoteResource, $this->keepUnchangedCloudValues($remoteResource, $value));

        $remoteResourceLocation->setSource($value['source'] ?? null);
        $remoteResourceLocation->setWatermarkText($value['watermarkText'] ?? null);
        $remoteResourceLocation->setAltTextOverride($value['altTextOverride'] ?? null);
        $remoteResourceLocation->setCaptionOverride($value['captionOverride'] ?? null);

        $remoteResourceLocation->setCropSettings(
            $this->service->resolveCropSettings($value['cropSettings'] ?? null),
        );

        return $remoteResourceLocation;
    }

    /**
     * The same resource can be in several fields of one form, and each of them
     * submits its alt text, caption and tags. A value which the editor didn't
     * change in a field is replaced with the current one of the resource, so
     * the field doesn't undo a change made in another field.
     *
     * An entry without the values the widget loaded is used as it is.
     *
     * @param array<string, mixed> $value
     *
     * @return array<string, mixed>
     */
    private function keepUnchangedCloudValues(RemoteResource $remoteResource, array $value): array
    {
        $loaded = $value['loaded'] ?? null;
        if (!is_array($loaded)) {
            return $value;
        }

        if (($value['altText'] ?? null) === ($loaded['altText'] ?? null)) {
            $value['altText'] = $remoteResource->getAltText();
        }

        if (($value['caption'] ?? null) === ($loaded['caption'] ?? null)) {
            $value['caption'] = $remoteResource->getCaption();
        }

        $tags = $value['tags'] ?? [];
        $loadedTags = $loaded['tags'] ?? [];
        sort($tags);
        sort($loadedTags);

        if ($tags === $loadedTags) {
            $value['tags'] = $remoteResource->getTags();
        }

        return $value;
    }
}
