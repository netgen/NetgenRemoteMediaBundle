<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

use function array_filter;
use function array_key_exists;
use function array_values;
use function ctype_digit;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

/**
 * Shared extraction/normalization helpers for RemoteMedia collection submissions.
 * Used by the form types' PRE_SUBMIT listeners and RemoteMediaCollectionTransformer
 * so the two paths cannot drift out of sync.
 *
 * The canonical wire format is the JSON `collectionPayload` (see fromPayload()).
 * The root-indexed and field-indexed forms are deprecated fallbacks kept for
 * backwards compatibility with pre-payload clients and will be removed.
 */
final class RemoteMediaCollectionEntryExtractor
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromPayload(array $data): array
    {
        if (!array_key_exists('collectionPayload', $data)) {
            return [];
        }

        $payload = $data['collectionPayload'];
        if ($payload === '') {
            return [];
        }

        if (!is_string($payload)) {
            throw new TransformationFailedException('The remote media collection payload must be a JSON array.');
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException(
                'The remote media collection payload is not valid JSON: ' . json_last_error_msg(),
            );
        }

        if (!is_array($decoded)) {
            throw new TransformationFailedException('The remote media collection payload must be a JSON array.');
        }

        $entries = [];
        foreach ($decoded as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $normalized = self::normalizeEntry($entry, $data['source'] ?? null);
            if ($normalized !== null) {
                $entries[] = $normalized;
            }
        }

        return $entries;
    }

    /**
     * @deprecated submit the JSON `collectionPayload` instead; kept for BC with pre-payload clients
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromRootIndexed(array $data): array
    {
        $entries = [];
        foreach ($data as $index => $entry) {
            if ((!is_int($index) && !(is_string($index) && ctype_digit($index))) || !is_array($entry)) {
                continue;
            }

            $normalized = self::normalizeEntry($entry, $data['source'] ?? null);
            if ($normalized !== null) {
                $entries[] = $normalized;
            }
        }

        return $entries;
    }

    /**
     * @deprecated submit the JSON `collectionPayload` instead; kept for BC with pre-payload clients
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromFieldIndexed(array $data): array
    {
        if (!isset($data['remoteId']) || !is_array($data['remoteId'])) {
            return [];
        }

        $entries = [];
        foreach ($data['remoteId'] as $index => $_) {
            $remoteId = self::indexedValue($data, 'remoteId', $index);
            if (!is_string($remoteId) || $remoteId === '') {
                continue;
            }

            $entries[] = [
                'locationId' => self::indexedValue($data, 'locationId', $index),
                'remoteId' => $remoteId,
                'type' => (string) (self::indexedValue($data, 'type', $index, '') ?? ''),
                'altText' => (string) (self::indexedValue($data, 'altText', $index, '') ?? ''),
                'caption' => (string) (self::indexedValue($data, 'caption', $index, '') ?? ''),
                'watermarkText' => (string) (self::indexedValue($data, 'watermarkText', $index, '') ?? ''),
                'tags' => self::normalizeTags(self::indexedTags($data, $index)),
                'cropSettings' => (string) (self::indexedValue($data, 'cropSettings', $index, '{}') ?? '{}'),
                'source' => (string) (self::indexedValue($data, 'source', $index, $data['source'] ?? null) ?? ''),
            ];
        }

        return $entries;
    }

    /**
     * Tries payload first, then root-indexed, then field-indexed. Returns the first non-empty result, or [].
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, array<string, mixed>>
     */
    public static function extractAny(array $data): array
    {
        $entries = self::fromPayload($data);
        if ($entries === []) {
            $entries = self::fromRootIndexed($data);
        }
        if ($entries === []) {
            $entries = self::fromFieldIndexed($data);
        }

        return $entries;
    }

    /**
     * Removes numeric top-level keys from the data so they cannot leak through to scalar HiddenType children.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function stripNumericKeys(array $data): array
    {
        foreach ($data as $key => $_) {
            if (is_int($key) || (is_string($key) && ctype_digit($key))) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $entry
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeEntry(array $entry, mixed $source): ?array
    {
        $remoteId = $entry['remoteId'] ?? $entry['id'] ?? null;
        if (!is_string($remoteId) || $remoteId === '') {
            return null;
        }

        $locationId = $entry['locationId'] ?? null;
        if ($locationId !== null && (!is_scalar($locationId) || $locationId === '')) {
            $locationId = null;
        }

        return [
            'locationId' => $locationId,
            'remoteId' => $remoteId,
            'type' => (string) ($entry['type'] ?? ''),
            'altText' => (string) ($entry['altText'] ?? $entry['alternateText'] ?? ''),
            'caption' => (string) ($entry['caption'] ?? ''),
            'watermarkText' => (string) ($entry['watermarkText'] ?? ''),
            'tags' => self::normalizeTags($entry['tags'] ?? []),
            'cropSettings' => (string) ($entry['cropSettings'] ?? '{}'),
            'source' => (string) ($entry['source'] ?? $source ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function indexedValue(array $data, string $field, int|string $index, mixed $default = null): mixed
    {
        if (!array_key_exists($field, $data)) {
            return $default;
        }

        $value = $data[$field];
        if (!is_array($value)) {
            return $value;
        }

        if (!array_key_exists($index, $value)) {
            return $default;
        }

        $indexedValue = $value[$index];
        if (is_array($indexedValue) && array_key_exists($field, $indexedValue)) {
            return $indexedValue[$field];
        }

        if (!is_array($indexedValue)) {
            return $indexedValue;
        }

        return $default;
    }

    /**
     * Tags are the one field whose per-index value is itself a list, so the
     * scalar-oriented indexedValue() helper cannot be used for them.
     *
     * @param array<string, mixed> $data
     */
    private static function indexedTags(array $data, int|string $index): mixed
    {
        $tags = $data['tags'] ?? null;
        if (!is_array($tags) || !array_key_exists($index, $tags)) {
            return [];
        }

        $indexedTags = $tags[$index];
        if (is_array($indexedTags) && array_key_exists('tags', $indexedTags)) {
            return $indexedTags['tags'];
        }

        return $indexedTags;
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeTags(mixed $tags): array
    {
        if (!is_array($tags)) {
            return [];
        }

        return array_values(
            array_filter(
                $tags,
                static fn ($tag): bool => is_string($tag) && $tag !== '',
            ),
        );
    }
}
