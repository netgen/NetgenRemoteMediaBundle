<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

use function array_is_list;
use function array_key_exists;
use function ctype_digit;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;

use const JSON_ERROR_NONE;

/**
 * Reads the JSON `collectionPayload` submitted by the remote media widget into
 * normalized entries, one per selected resource, in the submitted order.
 *
 * Fields which are not part of an entry are dropped. That includes `altText`
 * and `caption`: the form field edits only the overrides of a location.
 *
 * Shared by RemoteMediaType and RemoteMediaCollectionTransformer, so both read
 * the payload in the same way.
 */
final class RemoteMediaCollectionEntryExtractor
{
    private const STRING_FIELDS = ['type', 'altTextOverride', 'captionOverride', 'watermarkText'];

    private const CROP_COORDINATES = ['x', 'y', 'w', 'h', 'width', 'height'];

    /**
     * Entries without a remote ID are skipped. Of the entries repeating a
     * location ID only the first one is kept, as a location can be in the
     * collection only once.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws TransformationFailedException If the payload is not a JSON list of objects, or an entry field has an unexpected type
     */
    public static function fromPayload(mixed $payload, ?string $defaultSource = null): array
    {
        $entries = [];
        foreach (self::decode($payload) as $entry) {
            $entry = self::normalizeEntry($entry, $defaultSource);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return self::removeRepeatedLocations($entries);
    }

    /**
     * Same as fromPayload(), but skips everything that cannot be read instead
     * of failing. Meant for showing a rejected submission back to the editor.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromPayloadLenient(mixed $payload, ?string $defaultSource = null): array
    {
        try {
            $decoded = self::decode($payload);
        } catch (TransformationFailedException) {
            return [];
        }

        $entries = [];
        foreach ($decoded as $entry) {
            try {
                $entry = self::normalizeEntry($entry, $defaultSource);
            } catch (TransformationFailedException) {
                continue;
            }

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return self::removeRepeatedLocations($entries);
    }

    /**
     * @return array<int, mixed>
     */
    private static function decode(mixed $payload): array
    {
        if (!is_string($payload) || $payload === '') {
            throw new TransformationFailedException(
                sprintf('The remote media collection payload must be a JSON string, %s given.', $payload === '' ? 'empty string' : get_debug_type($payload)),
            );
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException(
                'The remote media collection payload is not valid JSON: ' . json_last_error_msg(),
            );
        }

        // An empty JSON object decodes to an empty array as well, but only "[]" may clear the collection.
        if (!is_array($decoded) || !array_is_list($decoded) || ($decoded === [] && !is_array(json_decode($payload)))) {
            throw new TransformationFailedException('The remote media collection payload must be a JSON array.');
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeEntry(mixed $entry, ?string $defaultSource): ?array
    {
        if (!is_array($entry)) {
            throw new TransformationFailedException(
                sprintf('A remote media collection entry must be a JSON object, %s given.', get_debug_type($entry)),
            );
        }

        $remoteId = self::stringField($entry, 'remoteId');
        if ($remoteId === null) {
            return null;
        }

        $normalized = [
            'locationId' => self::locationId($entry['locationId'] ?? null),
            'remoteId' => $remoteId,
        ];

        foreach (self::STRING_FIELDS as $field) {
            $normalized[$field] = self::stringField($entry, $field);
        }

        $normalized['tags'] = self::tags($entry['tags'] ?? null);
        $normalized['cropSettings'] = self::cropSettings($entry['cropSettings'] ?? null);
        $normalized['source'] = self::stringField($entry, 'source') ?? $defaultSource;

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     *
     * @return array<int, array<string, mixed>>
     */
    private static function removeRepeatedLocations(array $entries): array
    {
        $seenLocationIds = [];
        $uniqueEntries = [];

        foreach ($entries as $entry) {
            $locationId = $entry['locationId'];
            if ($locationId !== null) {
                if (array_key_exists($locationId, $seenLocationIds)) {
                    continue;
                }

                $seenLocationIds[$locationId] = true;
            }

            $uniqueEntries[] = $entry;
        }

        return $uniqueEntries;
    }

    /**
     * @param array<mixed> $entry
     *
     * @return string|null Null if the field is missing or empty
     */
    private static function stringField(array $entry, string $field): ?string
    {
        $value = $entry[$field] ?? null;
        if ($value !== null && !is_string($value)) {
            throw self::unexpectedType($field, 'a string', $value);
        }

        return $value !== '' ? $value : null;
    }

    private static function locationId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $value = (int) $value;
        }

        if (!is_int($value) || $value < 1) {
            throw self::unexpectedType('locationId', 'a positive integer', $value);
        }

        return $value;
    }

    /**
     * @return array<int, string>
     */
    private static function tags(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            throw self::unexpectedType('tags', 'a list of strings', $value);
        }

        $tags = [];
        foreach ($value as $tag) {
            if (!is_string($tag)) {
                throw self::unexpectedType('tags', 'a list of strings', $value);
            }

            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * Crop settings travel as a JSON string of their own: an object holding
     * the crop coordinates per variation name.
     */
    private static function cropSettings(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '{}';
        }

        if (!is_string($value)) {
            throw self::unexpectedType('cropSettings', 'a JSON string', $value);
        }

        $variations = json_decode($value, true);
        if (!is_array($variations)) {
            throw self::unexpectedType('cropSettings', 'a JSON string holding an object', $variations);
        }

        foreach ($variations as $coordinates) {
            if (!is_array($coordinates)) {
                continue;
            }

            foreach (self::CROP_COORDINATES as $name) {
                $coordinate = $coordinates[$name] ?? null;
                if ($coordinate !== null && !is_int($coordinate)) {
                    throw self::unexpectedType('cropSettings', 'a JSON string with integer crop coordinates', $coordinate);
                }
            }
        }

        return $value;
    }

    private static function unexpectedType(string $field, string $expected, mixed $value): TransformationFailedException
    {
        return new TransformationFailedException(
            sprintf(
                'The "%s" field of a remote media collection entry must be %s, %s given.',
                $field,
                $expected,
                get_debug_type($value),
            ),
        );
    }
}
