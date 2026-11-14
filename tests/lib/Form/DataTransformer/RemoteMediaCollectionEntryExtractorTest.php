<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function array_column;
use function json_encode;

#[CoversClass(RemoteMediaCollectionEntryExtractor::class)]
final class RemoteMediaCollectionEntryExtractorTest extends TestCase
{
    public function testFromPayloadNormalizesEntries(): void
    {
        $payload = json_encode([
            [
                'locationId' => 12,
                'remoteId' => 'upload|image|first.jpg',
                'type' => 'image',
                'altText' => 'Alt',
                'caption' => 'Caption',
                'altTextOverride' => 'Local alt',
                'captionOverride' => 'Local caption',
                'watermarkText' => 'Watermark',
                'tags' => ['one', '', 'two'],
                'cropSettings' => '{"hero":{"x":1,"y":2,"w":300,"h":200}}',
                'source' => 'own_source',
            ],
            [
                'locationId' => '',
                'remoteId' => 'upload|image|second.jpg',
                'captionOverride' => '',
                'source' => '',
            ],
            [
                'locationId' => '15',
                'remoteId' => 'upload|image|third.jpg',
                'altTextOverride' => null,
                'tags' => null,
                'cropSettings' => null,
            ],
        ]);

        self::assertSame(
            [
                [
                    'locationId' => 12,
                    'remoteId' => 'upload|image|first.jpg',
                    'type' => 'image',
                    'altTextOverride' => 'Local alt',
                    'captionOverride' => 'Local caption',
                    'watermarkText' => 'Watermark',
                    'tags' => ['one', 'two'],
                    'cropSettings' => '{"hero":{"x":1,"y":2,"w":300,"h":200}}',
                    'source' => 'own_source',
                ],
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|second.jpg',
                    'type' => null,
                    'altTextOverride' => null,
                    'captionOverride' => null,
                    'watermarkText' => null,
                    'tags' => [],
                    'cropSettings' => '{}',
                    'source' => 'form_gallery',
                ],
                [
                    'locationId' => 15,
                    'remoteId' => 'upload|image|third.jpg',
                    'type' => null,
                    'altTextOverride' => null,
                    'captionOverride' => null,
                    'watermarkText' => null,
                    'tags' => [],
                    'cropSettings' => '{}',
                    'source' => 'form_gallery',
                ],
            ],
            RemoteMediaCollectionEntryExtractor::fromPayload($payload, 'form_gallery'),
        );
    }

    /**
     * The form field edits only the overrides, so the alt text and caption of
     * the resource are not part of an entry, whatever is sent for them.
     */
    public function testFromPayloadDropsAltTextAndCaptionOfTheResource(): void
    {
        $entries = RemoteMediaCollectionEntryExtractor::fromPayload(json_encode([
            ['remoteId' => 'r1', 'altText' => 'Alt', 'caption' => 'Caption', 'altTextOverride' => 'Local alt'],
            ['remoteId' => 'r2', 'altText' => ['en' => 'Alt'], 'caption' => 1.5],
        ]));

        self::assertSame(['r1', 'r2'], array_column($entries, 'remoteId'));
        self::assertSame(['Local alt', null], array_column($entries, 'altTextOverride'));

        foreach ($entries as $entry) {
            self::assertArrayNotHasKey('altText', $entry);
            self::assertArrayNotHasKey('caption', $entry);
        }
    }

    public function testFromPayloadReturnsNoEntriesForEmptyList(): void
    {
        self::assertSame([], RemoteMediaCollectionEntryExtractor::fromPayload('[]'));
    }

    public function testFromPayloadSkipsEntriesWithoutRemoteId(): void
    {
        $entries = RemoteMediaCollectionEntryExtractor::fromPayload(json_encode([
            ['remoteId' => 'r1'],
            ['captionOverride' => 'no remote ID'],
            ['remoteId' => ''],
            ['remoteId' => null],
            ['remoteId' => 'r2'],
        ]));

        self::assertSame(['r1', 'r2'], array_column($entries, 'remoteId'));
    }

    public function testFromPayloadKeepsOnlyTheFirstEntryOfARepeatedLocation(): void
    {
        $entries = RemoteMediaCollectionEntryExtractor::fromPayload(json_encode([
            ['locationId' => 6, 'remoteId' => 'r1', 'captionOverride' => 'first'],
            ['locationId' => 7, 'remoteId' => 'r2'],
            ['locationId' => '6', 'remoteId' => 'r1', 'captionOverride' => 'repeated'],
            ['locationId' => null, 'remoteId' => 'r1', 'captionOverride' => 'new location of the same resource'],
            ['locationId' => '', 'remoteId' => 'r1', 'captionOverride' => 'another new location'],
        ]));

        self::assertSame([6, 7, null, null], array_column($entries, 'locationId'));
        self::assertSame(
            ['first', null, 'new location of the same resource', 'another new location'],
            array_column($entries, 'captionOverride'),
        );
    }

    #[DataProvider('invalidPayloadDataProvider')]
    public function testFromPayloadRejectsInvalidPayload(mixed $payload): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('The remote media collection payload');

        RemoteMediaCollectionEntryExtractor::fromPayload($payload);
    }

    #[DataProvider('invalidEntryDataProvider')]
    public function testFromPayloadRejectsEntryFieldsOfUnexpectedType(mixed $entry, string $expectedMessage): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage($expectedMessage);

        RemoteMediaCollectionEntryExtractor::fromPayload(json_encode([$entry]));
    }

    public function testFromPayloadLenientSkipsWhatCannotBeRead(): void
    {
        $entries = RemoteMediaCollectionEntryExtractor::fromPayloadLenient(
            json_encode([
                ['remoteId' => 'r1', 'cropSettings' => ['hero' => ['x' => 1]]],
                'not an entry',
                ['remoteId' => 'r2', 'captionOverride' => 'kept'],
                ['remoteId' => 'r3', 'tags' => 'not a list'],
                ['locationId' => 4, 'remoteId' => 'r4'],
                ['locationId' => 4, 'remoteId' => 'r4'],
            ]),
            'form_gallery',
        );

        self::assertSame(['r2', 'r4'], array_column($entries, 'remoteId'));
        self::assertSame(['kept', null], array_column($entries, 'captionOverride'));
        self::assertSame(['form_gallery', 'form_gallery'], array_column($entries, 'source'));
    }

    #[DataProvider('invalidPayloadDataProvider')]
    public function testFromPayloadLenientReturnsNoEntriesForInvalidPayload(mixed $payload): void
    {
        self::assertSame([], RemoteMediaCollectionEntryExtractor::fromPayloadLenient($payload));
    }

    public static function invalidPayloadDataProvider(): iterable
    {
        return [
            'missing' => [null],
            'empty string' => [''],
            'not a string' => [['remoteId' => 'r1']],
            'malformed JSON' => ['{"remoteId":'],
            'JSON scalar' => ['"r1"'],
            'JSON object' => ['{"remoteId":"r1"}'],
            'empty JSON object' => ['{}'],
            'JSON null' => ['null'],
        ];
    }

    public static function invalidEntryDataProvider(): iterable
    {
        return [
            'entry is a string' => [
                'r1',
                'entry must be a JSON object, string given',
            ],
            'remote ID is a number' => [
                ['remoteId' => 5],
                'The "remoteId" field of a remote media collection entry must be a string, int given',
            ],
            'location ID is a list' => [
                ['remoteId' => 'r1', 'locationId' => ['nested']],
                'The "locationId" field of a remote media collection entry must be a positive integer, array given',
            ],
            'location ID is not numeric' => [
                ['remoteId' => 'r1', 'locationId' => 'abc'],
                'The "locationId" field of a remote media collection entry must be a positive integer, string given',
            ],
            'location ID is zero' => [
                ['remoteId' => 'r1', 'locationId' => 0],
                'The "locationId" field of a remote media collection entry must be a positive integer, int given',
            ],
            'caption override is a number' => [
                ['remoteId' => 'r1', 'captionOverride' => 1.5],
                'The "captionOverride" field of a remote media collection entry must be a string, float given',
            ],
            'alt text override is a boolean' => [
                ['remoteId' => 'r1', 'altTextOverride' => true],
                'The "altTextOverride" field of a remote media collection entry must be a string, bool given',
            ],
            'watermark text is a list' => [
                ['remoteId' => 'r1', 'watermarkText' => ['text']],
                'The "watermarkText" field of a remote media collection entry must be a string, array given',
            ],
            'source is a list' => [
                ['remoteId' => 'r1', 'source' => ['form']],
                'The "source" field of a remote media collection entry must be a string, array given',
            ],
            'tags are a string' => [
                ['remoteId' => 'r1', 'tags' => 'one,two'],
                'The "tags" field of a remote media collection entry must be a list of strings, string given',
            ],
            'a tag is a list' => [
                ['remoteId' => 'r1', 'tags' => ['one', ['two']]],
                'The "tags" field of a remote media collection entry must be a list of strings, array given',
            ],
            'crop settings are an object' => [
                ['remoteId' => 'r1', 'cropSettings' => ['hero' => ['x' => 1, 'y' => 2, 'w' => 3, 'h' => 4]]],
                'The "cropSettings" field of a remote media collection entry must be a JSON string, array given',
            ],
            'crop settings are an empty object' => [
                ['remoteId' => 'r1', 'cropSettings' => new stdClass()],
                'The "cropSettings" field of a remote media collection entry must be a JSON string, array given',
            ],
            'crop settings are not JSON' => [
                ['remoteId' => 'r1', 'cropSettings' => 'hero'],
                'The "cropSettings" field of a remote media collection entry must be a JSON string holding an object, null given',
            ],
            'crop coordinate is a string' => [
                ['remoteId' => 'r1', 'cropSettings' => '{"hero":{"x":"1","y":2,"w":3,"h":4}}'],
                'The "cropSettings" field of a remote media collection entry must be a JSON string with integer crop coordinates, string given',
            ],
        ];
    }
}
