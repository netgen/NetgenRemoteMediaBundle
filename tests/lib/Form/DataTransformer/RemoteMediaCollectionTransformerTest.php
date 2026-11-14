<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaTransformer;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function array_column;
use function json_decode;
use function json_encode;

#[CoversClass(RemoteMediaCollectionTransformer::class)]
final class RemoteMediaCollectionTransformerTest extends AbstractTestCase
{
    private DataTransformerInterface|MockObject $innerTransformer;

    private RemoteMediaCollectionTransformer $transformer;

    protected function setUp(): void
    {
        $this->innerTransformer = $this->createMock(DataTransformerInterface::class);
        $this->transformer = new RemoteMediaCollectionTransformer($this->innerTransformer);
    }

    public function testTransformsCollectionToPayload(): void
    {
        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('transform')
            ->willReturnOnConsecutiveCalls(
                ['remoteId' => 'remote-1'],
                ['remoteId' => 'remote-2'],
            );

        self::assertSame(
            [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'remote-1'],
                    ['remoteId' => 'remote-2'],
                ]),
            ],
            $this->transformer->transform(new ArrayCollection([self::createLocation('remote-1'), self::createLocation('remote-2')])),
        );
    }

    #[DataProvider('emptyModelDataProvider')]
    public function testTransformsEmptyModelDataToEmptyList(mixed $value): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('transform');

        self::assertSame(['collectionPayload' => '[]'], $this->transformer->transform($value));
    }

    public function testTransformSkipsNonLocationItems(): void
    {
        $this->innerTransformer
            ->expects(self::once())
            ->method('transform')
            ->willReturn(['remoteId' => 'remote-1']);

        self::assertSame(
            ['collectionPayload' => json_encode([['remoteId' => 'remote-1']])],
            $this->transformer->transform(['not a location', self::createLocation('remote-1')]),
        );
    }

    public function testTransformRejectsSingleLocation(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('transform');

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('The data of RemoteMediaType is a collection, also when "upload_limit" is 1.');

        $this->transformer->transform(self::createLocation('remote-1'));
    }

    public function testTransformOrdersLocationsByPositionThenId(): void
    {
        $locations = [
            self::createLocation('second', id: 3, position: 1),
            self::createLocation('unsaved-without-position'),
            self::createLocation('third-higher-id', id: 9, position: 2),
            self::createLocation('first', id: 8, position: 0),
            self::createLocation('without-position-higher-id', id: 7),
            self::createLocation('third-lower-id', id: 5, position: 2),
            self::createLocation('without-position-lower-id', id: 6),
            self::createLocation('third-unsaved', position: 2),
        ];

        $this->innerTransformer
            ->expects(self::exactly(8))
            ->method('transform')
            ->willReturnCallback(
                static fn (RemoteResourceLocation $location): array => ['remoteId' => $location->getRemoteResource()->getRemoteId()],
            );

        $viewData = $this->transformer->transform(new ArrayIterator($locations));

        self::assertSame(
            [
                // Without a position: first, in the order they were given.
                'unsaved-without-position',
                'without-position-higher-id',
                'without-position-lower-id',
                'first',
                'second',
                'third-lower-id',
                'third-higher-id',
                'third-unsaved',
            ],
            array_column(json_decode($viewData['collectionPayload'], true), 'remoteId'),
        );
    }

    public function testTransformKeepsGivenOrderOfLocationsWithoutPosition(): void
    {
        $this->innerTransformer
            ->expects(self::exactly(3))
            ->method('transform')
            ->willReturnCallback(
                static fn (RemoteResourceLocation $location): array => ['locationId' => $location->getId()],
            );

        $viewData = $this->transformer->transform(new ArrayCollection([
            self::createLocation('remote-8', id: 8),
            self::createLocation('remote-6', id: 6),
            self::createLocation('remote-7', id: 7),
        ]));

        self::assertSame([8, 6, 7], array_column(json_decode($viewData['collectionPayload'], true), 'locationId'));
    }

    public function testReverseTransformReturnsCollectionInSubmittedOrderAndSetsPositions(): void
    {
        $locations = [
            'remote-1' => self::createLocation('remote-1', position: 7),
            'remote-2' => self::createLocation('remote-2'),
            'remote-3' => self::createLocation('remote-3', position: 0),
        ];

        $this->innerTransformer
            ->expects(self::exactly(3))
            ->method('reverseTransform')
            ->willReturnCallback(static fn (array $entry): RemoteResourceLocation => $locations[$entry['remoteId']]);

        $result = $this->transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-3'],
                ['remoteId' => 'remote-1'],
                ['remoteId' => 'remote-2'],
            ]),
        ]);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertSame([$locations['remote-3'], $locations['remote-1'], $locations['remote-2']], $result->toArray());
        self::assertSame(0, $locations['remote-3']->getPosition());
        self::assertSame(1, $locations['remote-1']->getPosition());
        self::assertSame(2, $locations['remote-2']->getPosition());
    }

    public function testReverseTransformPassesNormalizedEntriesWithDefaultSourceToInnerTransformer(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, null, 'form_gallery');

        $this->innerTransformer
            ->expects(self::once())
            ->method('reverseTransform')
            ->with([
                'locationId' => 4,
                'remoteId' => 'remote-1',
                'type' => 'image',
                'altTextOverride' => null,
                'captionOverride' => null,
                'watermarkText' => null,
                'tags' => [],
                'cropSettings' => '{}',
                'source' => 'form_gallery',
            ])
            ->willReturn(self::createLocation('remote-1'));

        $transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['locationId' => '4', 'remoteId' => 'remote-1', 'type' => 'image'],
            ]),
        ]);
    }

    public function testReverseTransformOfEmptyListReturnsEmptyCollection(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $result = $this->transformer->reverseTransform(['collectionPayload' => '[]']);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    #[DataProvider('missingPayloadDataProvider')]
    public function testReverseTransformRejectsMissingPayload(mixed $value): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform($value);
    }

    public function testReverseTransformRejectsMalformedPayload(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform(['collectionPayload' => '{"remoteId":']);
    }

    public function testReverseTransformRejectsEntryFieldsOfUnexpectedType(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('The "cropSettings" field of a remote media collection entry must be a JSON string, array given.');

        $this->transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'cropSettings' => ['hero' => ['x' => 0, 'y' => 0, 'w' => 10, 'h' => 10]]],
            ]),
        ]);
    }

    public function testReverseTransformKeepsOnlyTheFirstEntryOfARepeatedLocation(): void
    {
        $location = self::createLocation('remote-1', id: 6);

        $this->innerTransformer
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => $entry['captionOverride'] === 'first'))
            ->willReturn($location);

        $result = $this->transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['locationId' => 6, 'remoteId' => 'remote-1', 'captionOverride' => 'first'],
                ['locationId' => 6, 'remoteId' => 'remote-1', 'captionOverride' => 'repeated'],
            ]),
        ]);

        self::assertSame([$location], $result->toArray());
    }

    public function testReverseTransformSkipsEntriesTheInnerTransformerCannotResolve(): void
    {
        $location = self::createLocation('remote-2');

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnOnConsecutiveCalls(null, $location);

        $result = $this->transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1'],
                ['remoteId' => 'remote-2'],
            ]),
        ]);

        self::assertSame([$location], $result->toArray());
        self::assertSame(0, $location->getPosition());
    }

    public function testLimitOneReturnsCollectionWithOneLocation(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, 1);
        $location = self::createLocation('remote-1');

        $this->innerTransformer
            ->expects(self::once())
            ->method('reverseTransform')
            ->willReturn($location);

        $result = $transformer->reverseTransform([
            'collectionPayload' => json_encode([['remoteId' => 'remote-1']]),
        ]);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertSame([$location], $result->toArray());
    }

    public function testLimitOneReturnsEmptyCollectionForEmptyList(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, 1);

        $result = $transformer->reverseTransform(['collectionPayload' => '[]']);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    public function testCollectionAtUploadLimitIsAccepted(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, 2);

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnOnConsecutiveCalls(self::createLocation('remote-1'), self::createLocation('remote-2'));

        $result = $transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1'],
                ['remoteId' => 'remote-2'],
            ]),
        ]);

        self::assertCount(2, $result);
    }

    public function testCollectionOverUploadLimitIsRejectedWithTranslatableMessage(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, 2);

        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        try {
            $transformer->reverseTransform([
                'collectionPayload' => json_encode([
                    ['remoteId' => 'remote-1'],
                    ['remoteId' => 'remote-2'],
                    ['remoteId' => 'remote-3'],
                ]),
            ]);

            self::fail('The collection over the upload limit was accepted.');
        } catch (TransformationFailedException $exception) {
            self::assertSame('ngrm.form.upload_limit_exceeded', $exception->getInvalidMessage());
            self::assertSame(['{{ count }}' => '3', '{{ limit }}' => '2'], $exception->getInvalidMessageParameters());
        }
    }

    public function testRepeatedLocationsDoNotCountTowardsUploadLimit(): void
    {
        $transformer = new RemoteMediaCollectionTransformer($this->innerTransformer, 1);
        $location = self::createLocation('remote-1', id: 6);

        $this->innerTransformer
            ->expects(self::once())
            ->method('reverseTransform')
            ->willReturn($location);

        $result = $transformer->reverseTransform([
            'collectionPayload' => json_encode([
                ['locationId' => 6, 'remoteId' => 'remote-1'],
                ['locationId' => 6, 'remoteId' => 'remote-1'],
            ]),
        ]);

        self::assertSame([$location], $result->toArray());
    }

    public function testReorderAndRemovalOfPersistedLocationsThroughRealInnerTransformer(): void
    {
        $locations = [];
        foreach ([6, 7, 8] as $position => $id) {
            $locations[$id] = self::createLocation('upload|image|' . $id . '.jpg', id: $id, position: $position);
        }

        $transformer = new RemoteMediaCollectionTransformer($this->createRealInnerTransformer($locations));

        $entries = json_decode($transformer->transform(new ArrayCollection($locations))['collectionPayload'], true);
        self::assertSame([6, 7, 8], array_column($entries, 'locationId'));

        // The editor moves the last location to the front and removes the one in the middle.
        $result = $transformer->reverseTransform([
            'collectionPayload' => json_encode([$entries[2], $entries[0]]),
        ]);

        self::assertSame([$locations[8], $locations[6]], $result->toArray());
        self::assertSame(0, $locations[8]->getPosition());
        self::assertSame(1, $locations[6]->getPosition());

        // What was stored is shown in the stored order, whatever order the collection is loaded in.
        $entries = json_decode(
            $transformer->transform(new ArrayCollection([$locations[6], $locations[8]]))['collectionPayload'],
            true,
        );
        self::assertSame([8, 6], array_column($entries, 'locationId'));
    }

    public function testMixedCreateUpdateDeletePayloadThroughRealInnerTransformer(): void
    {
        $existingResource = new RemoteResource(
            'upload|image|existing.jpg',
            RemoteResource::TYPE_IMAGE,
            'https://example.test/existing.jpg',
            'md5-existing',
            tags: ['old'],
        );
        $newResource = new RemoteResource(
            'upload|image|new.jpg',
            RemoteResource::TYPE_IMAGE,
            'https://example.test/new.jpg',
            'md5-new',
        );
        $existingLocation = new RemoteResourceLocation($existingResource, 'old-source', id: 10);

        $provider = $this->createMock(ProviderInterface::class);
        $provider
            ->expects(self::exactly(2))
            ->method('loadByRemoteId')
            ->willReturnCallback(static fn (string $remoteId): RemoteResource => match ($remoteId) {
                'upload|image|existing.jpg' => $existingResource,
                'upload|image|new.jpg' => throw new RemoteResourceNotFoundException($remoteId),
            });
        $provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|image|new.jpg')
            ->willReturn($newResource);
        $provider
            ->expects(self::once())
            ->method('loadLocation')
            ->with(10)
            ->willReturn($existingLocation);
        $provider
            ->expects(self::exactly(2))
            ->method('updateOnRemote');

        $transformer = new RemoteMediaCollectionTransformer(
            new RemoteMediaTransformer($provider, new RemoteResourceService($provider)),
        );

        $result = $transformer->reverseTransform([
            'collectionPayload' => json_encode([
                [
                    'locationId' => '10',
                    'remoteId' => 'upload|image|existing.jpg',
                    'altText' => 'Updated alt',
                    'caption' => 'Updated caption',
                    'tags' => ['updated'],
                    'cropSettings' => '{}',
                    'source' => 'updated-source',
                ],
                [
                    'remoteId' => 'upload|image|new.jpg',
                    'altText' => 'New alt',
                    'caption' => 'New caption',
                    'tags' => ['new'],
                    'cropSettings' => '{}',
                    'source' => 'new-source',
                ],
            ]),
        ]);

        self::assertCount(2, $result);
        self::assertSame($existingLocation, $result[0]);
        self::assertSame($newResource, $result[1]->getRemoteResource());
        self::assertSame(['updated'], $existingResource->getTags());
        self::assertSame(['new'], $newResource->getTags());
        // Alt text and caption of a resource are never taken from the payload.
        self::assertNull($existingResource->getAltText());
        self::assertNull($existingResource->getCaption());
        self::assertNull($newResource->getAltText());
        self::assertNull($newResource->getCaption());
        self::assertSame('updated-source', $existingLocation->getSource());
        self::assertSame('new-source', $result[1]->getSource());
        self::assertSame(0, $existingLocation->getPosition());
        self::assertSame(1, $result[1]->getPosition());
    }

    public function testOverridesAreCarriedThroughCollectionPayload(): void
    {
        $firstLocation = self::createLocation('upload|image|first.jpg', id: 10);
        $firstLocation->setAltTextOverride('Old local alt')->setCaptionOverride('Old local caption');

        $secondLocation = self::createLocation('upload|image|second.jpg', id: 11);
        $secondLocation->setAltTextOverride('Kept local alt')->setCaptionOverride('Kept local caption');

        $transformer = new RemoteMediaCollectionTransformer(
            $this->createRealInnerTransformer([10 => $firstLocation, 11 => $secondLocation]),
        );

        $viewData = $transformer->transform(new ArrayCollection([$firstLocation, $secondLocation]));
        $entries = json_decode($viewData['collectionPayload'], true);

        self::assertSame('Old local alt', $entries[0]['altTextOverride']);
        self::assertSame('Old local caption', $entries[0]['captionOverride']);
        self::assertSame('Kept local alt', $entries[1]['altTextOverride']);
        self::assertSame('Kept local caption', $entries[1]['captionOverride']);

        $entries[0]['altTextOverride'] = 'New local alt';
        $entries[0]['captionOverride'] = '';

        $result = $transformer->reverseTransform(['collectionPayload' => json_encode($entries)]);

        self::assertSame([$firstLocation, $secondLocation], $result->toArray());
        self::assertSame('New local alt', $firstLocation->getAltTextOverride());
        self::assertNull($firstLocation->getCaptionOverride());
        self::assertSame('Kept local alt', $secondLocation->getAltTextOverride());
        self::assertSame('Kept local caption', $secondLocation->getCaptionOverride());
    }

    public static function emptyModelDataProvider(): iterable
    {
        return [
            'null' => [null],
            'empty array' => [[]],
            'empty collection' => [new ArrayCollection()],
        ];
    }

    public static function missingPayloadDataProvider(): iterable
    {
        return [
            'null' => [null],
            'string' => ['[]'],
            'no payload key' => [[]],
            'null payload' => [['collectionPayload' => null]],
            'empty payload' => [['collectionPayload' => '']],
        ];
    }

    private static function createLocation(string $remoteId, ?int $id = null, ?int $position = null): RemoteResourceLocation
    {
        return new RemoteResourceLocation(
            new RemoteResource($remoteId, RemoteResource::TYPE_IMAGE, 'https://example.test/' . $remoteId, 'md5-' . $remoteId),
            id: $id,
            position: $position,
        );
    }

    /**
     * The transformer of a single location on top of a provider which knows the given, already stored locations.
     *
     * @param array<int, RemoteResourceLocation> $locations Indexed by location ID
     */
    private function createRealInnerTransformer(array $locations): RemoteMediaTransformer
    {
        $resources = [];
        foreach ($locations as $location) {
            $resources[$location->getRemoteResource()->getRemoteId()] = $location->getRemoteResource();
        }

        $provider = $this->createMock(ProviderInterface::class);
        $provider
            ->method('loadByRemoteId')
            ->willReturnCallback(static fn (string $remoteId): RemoteResource => $resources[$remoteId]);
        $provider
            ->method('loadLocation')
            ->willReturnCallback(static fn (int $id): RemoteResourceLocation => $locations[$id]);
        $provider
            ->expects(self::never())
            ->method('updateOnRemote');

        return new RemoteMediaTransformer($provider, new RemoteResourceService($provider));
    }
}
