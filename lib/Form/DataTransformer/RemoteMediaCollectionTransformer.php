<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function count;
use function is_array;
use function is_iterable;
use function is_numeric;

/**
 * Model transformer for RemoteMediaCollectionType.
 *
 * Model data is always an ordered collection of RemoteResourceLocation objects
 * (an ArrayCollection, possibly empty) — never a bare location, never null.
 * Per-entry transformation is delegated to the single-resource transformer.
 *
 * @implements DataTransformerInterface<mixed, mixed>
 */
final class RemoteMediaCollectionTransformer implements DataTransformerInterface
{
    /**
     * @param DataTransformerInterface<mixed, mixed> $inner
     */
    public function __construct(
        private DataTransformerInterface $inner,
    ) {}

    /**
     * @param mixed $value
     *
     * @return array<int, array<string, mixed>>
     */
    public function transform($value): array
    {
        if (!is_iterable($value)) {
            return [];
        }

        $entries = [];
        foreach ($value as $item) {
            if (!$item instanceof RemoteResourceLocation) {
                continue;
            }

            $entry = $this->inner->transform($item);
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @param mixed $value
     *
     * @return ArrayCollection<int, RemoteResourceLocation>
     */
    public function reverseTransform($value): ArrayCollection
    {
        if (!is_array($value)) {
            return new ArrayCollection();
        }

        $entries = RemoteMediaCollectionEntryExtractor::extractAny($value);

        $uploadLimit = $this->resolveUploadLimit($value);
        if ($uploadLimit > 0 && count($entries) > $uploadLimit) {
            throw new TransformationFailedException(
                'The selected remote media collection exceeds the configured upload limit.',
                0,
                null,
                'The selected remote media collection exceeds the configured upload limit.',
            );
        }

        $locations = [];
        foreach ($entries as $entry) {
            $location = $this->inner->reverseTransform($entry);
            if ($location instanceof RemoteResourceLocation) {
                $locations[] = $location;
            }
        }

        return new ArrayCollection($locations);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveUploadLimit(array $data): int
    {
        $raw = $data['uploadLimit'] ?? null;

        // 0 (or absent/non-numeric) means unlimited for the collection type.
        return is_numeric($raw) ? (int) $raw : 0;
    }
}
