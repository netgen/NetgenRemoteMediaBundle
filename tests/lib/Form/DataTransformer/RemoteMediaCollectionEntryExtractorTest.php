<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function json_encode;

#[CoversClass(RemoteMediaCollectionEntryExtractor::class)]
final class RemoteMediaCollectionEntryExtractorTest extends TestCase
{
    public function testStripNumericKeysRemovesIntegerAndDigitStringKeys(): void
    {
        $data = [
            'remoteId' => 'r1',
            0 => ['remoteId' => 'r2'],
            '1' => ['remoteId' => 'r3'],
            'source' => 'form_source',
        ];

        $cleaned = RemoteMediaCollectionEntryExtractor::stripNumericKeys($data);

        self::assertSame(['remoteId' => 'r1', 'source' => 'form_source'], $cleaned);
    }

    /**
     * Regression: malformed indexed entries (no remoteId) used to leak through to scalar HiddenType
     * children because the PRE_SUBMIT listener bailed before stripping numeric keys. The extractor's
     * stripNumericKeys is now called unconditionally; this asserts it cleans the data even when no
     * usable entries are extracted.
     */
    public function testExtractAnyReturnsEmptyForMalformedIndexedEntries(): void
    {
        $data = [
            0 => ['caption' => 'orphan with no remoteId'],
            'remoteId' => '',
            'source' => 'form_source',
        ];

        self::assertSame([], RemoteMediaCollectionEntryExtractor::extractAny($data));
    }

    public function testFromPayloadDecodesJsonAndNormalizesEntries(): void
    {
        $data = [
            'collectionPayload' => json_encode([
                ['remoteId' => 'r1', 'altText' => 'a'],
                ['id' => 'r2'], // 'id' is accepted as fallback for remoteId
                ['caption' => 'no id, dropped'],
            ]),
            'source' => 'form_source',
        ];

        $entries = RemoteMediaCollectionEntryExtractor::fromPayload($data);

        self::assertCount(2, $entries);
        self::assertSame('r1', $entries[0]['remoteId']);
        self::assertSame('a', $entries[0]['altText']);
        self::assertSame('r2', $entries[1]['remoteId']);
        self::assertSame('form_source', $entries[0]['source']);
    }

    public function testFromRootIndexedSkipsEntriesWithoutRemoteId(): void
    {
        $data = [
            0 => ['remoteId' => 'r1'],
            1 => ['caption' => 'no remoteId, dropped'],
            2 => ['remoteId' => ''],
            3 => ['remoteId' => 'r2'],
        ];

        $entries = RemoteMediaCollectionEntryExtractor::fromRootIndexed($data);

        self::assertCount(2, $entries);
        self::assertSame('r1', $entries[0]['remoteId']);
        self::assertSame('r2', $entries[1]['remoteId']);
    }

    public function testFromFieldIndexedReadsParallelArrays(): void
    {
        $data = [
            'remoteId' => ['r1', 'r2'],
            'altText' => ['alt1', 'alt2'],
            'tags' => [['t1'], ['t2', '']],
            'source' => 'form_source',
        ];

        $entries = RemoteMediaCollectionEntryExtractor::fromFieldIndexed($data);

        self::assertCount(2, $entries);
        self::assertSame('r1', $entries[0]['remoteId']);
        self::assertSame('alt1', $entries[0]['altText']);
        self::assertSame(['t1'], $entries[0]['tags']);
        self::assertSame(['t2'], $entries[1]['tags']);
    }
}
