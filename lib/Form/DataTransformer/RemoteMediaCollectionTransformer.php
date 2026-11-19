<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function count;
use function get_debug_type;
use function is_array;
use function is_iterable;
use function json_encode;
use function sprintf;
use function usort;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const PHP_INT_MAX;

/**
 * Model transformer of RemoteMediaType.
 *
 * Model data is an ordered collection of RemoteResourceLocation objects, also
 * when the upload limit is 1. View data is the JSON `collectionPayload` with
 * one entry per location. Transformation of a single entry is delegated to
 * the inner transformer.
 *
 * @implements DataTransformerInterface<mixed, mixed>
 */
final class RemoteMediaCollectionTransformer implements DataTransformerInterface
{
    /**
     * Shown when more entries are submitted than the upload limit allows.
     * Translated in the "validators" domain, as all form error messages are.
     */
    public const LIMIT_EXCEEDED_MESSAGE = 'ngrm.form.upload_limit_exceeded';

    /**
     * @param DataTransformerInterface<mixed, mixed> $inner
     * @param int|null $uploadLimit Highest number of entries accepted, null for no limit
     * @param string|null $defaultSource Source of the entries submitted without one
     */
    public function __construct(
        private DataTransformerInterface $inner,
        private ?int $uploadLimit = null,
        private ?string $defaultSource = null,
    ) {}

    /**
     * Returns the given locations ordered by position, then by ID.
     *
     * Locations without a position have nothing to be ordered by, so they keep
     * the order they were given in. They come first, as they do with ORDER BY
     * on MySQL.
     *
     * @param iterable<mixed> $value
     *
     * @return array<int, RemoteResourceLocation>
     */
    public static function sortByPosition(iterable $value): array
    {
        $withoutPosition = [];
        $withPosition = [];

        foreach ($value as $location) {
            if (!$location instanceof RemoteResourceLocation) {
                continue;
            }

            if ($location->getPosition() === null) {
                $withoutPosition[] = $location;
            } else {
                $withPosition[] = $location;
            }
        }

        usort(
            $withPosition,
            static fn (RemoteResourceLocation $a, RemoteResourceLocation $b): int => [$a->getPosition(), $a->getId() ?? PHP_INT_MAX]
                <=> [$b->getPosition(), $b->getId() ?? PHP_INT_MAX],
        );

        return [...$withoutPosition, ...$withPosition];
    }

    /**
     * @param mixed $value
     *
     * @return array{collectionPayload: string}
     */
    public function transform($value): array
    {
        if ($value !== null && !is_iterable($value)) {
            throw new TransformationFailedException(
                sprintf(
                    'Expected a collection of %s objects or null, %s given. The data of RemoteMediaType is a collection, also when "upload_limit" is 1.',
                    RemoteResourceLocation::class,
                    get_debug_type($value),
                ),
            );
        }

        $entries = [];
        foreach (self::sortByPosition($value ?? []) as $location) {
            $entry = $this->inner->transform($location);
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        $payload = json_encode($entries, JSON_INVALID_UTF8_SUBSTITUTE);

        return ['collectionPayload' => $payload !== false ? $payload : '[]'];
    }

    /**
     * @param mixed $value
     *
     * @return ArrayCollection<int, RemoteResourceLocation>
     */
    public function reverseTransform($value): ArrayCollection
    {
        $entries = RemoteMediaCollectionEntryExtractor::fromPayload(
            is_array($value) ? $value['collectionPayload'] ?? null : null,
            $this->defaultSource,
        );

        if ($this->uploadLimit !== null && count($entries) > $this->uploadLimit) {
            throw new TransformationFailedException(
                sprintf('The remote media collection has %d entries, the upload limit is %d.', count($entries), $this->uploadLimit),
                0,
                null,
                self::LIMIT_EXCEEDED_MESSAGE,
                ['{{ count }}' => (string) count($entries), '{{ limit }}' => (string) $this->uploadLimit],
            );
        }

        $locations = [];
        foreach ($entries as $entry) {
            $location = $this->inner->reverseTransform($entry);
            if ($location instanceof RemoteResourceLocation) {
                $locations[] = $location->setPosition(count($locations));
            }
        }

        return new ArrayCollection($locations);
    }
}
