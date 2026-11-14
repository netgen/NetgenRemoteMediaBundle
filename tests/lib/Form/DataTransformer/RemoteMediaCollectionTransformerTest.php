<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

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
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function in_array;
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

    public function testTransformsCollectionToPayloadShape(): void
    {
        $firstLocation = $this->createMock(RemoteResourceLocation::class);
        $secondLocation = $this->createMock(RemoteResourceLocation::class);

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
                'locationId' => null,
                'remoteId' => 'remote-1',
                'type' => null,
                'altText' => null,
                'caption' => null,
                'altTextOverride' => null,
                'captionOverride' => null,
                'watermarkText' => null,
                'tags' => [],
                'cropSettings' => null,
                'source' => null,
            ],
            $this->transformer->transform(new ArrayCollection([$firstLocation, $secondLocation])),
        );
    }

    public function testTransformsNullToEmptyList(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('transform');

        self::assertSame(['collectionPayload' => '[]'], $this->transformer->transform(null));
    }

    public function testTransformSkipsNonLocationItems(): void
    {
        $location = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::once())
            ->method('transform')
            ->willReturn(['remoteId' => 'remote-1']);

        self::assertSame(
            [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'remote-1'],
                ]),
                'locationId' => null,
                'remoteId' => 'remote-1',
                'type' => null,
                'altText' => null,
                'caption' => null,
                'altTextOverride' => null,
                'captionOverride' => null,
                'watermarkText' => null,
                'tags' => [],
                'cropSettings' => null,
                'source' => null,
            ],
            $this->transformer->transform(['not a location', $location]),
        );
    }

    public function testCollectionPayloadInputReturnsOrderedCollection(): void
    {
        $payload = [
            'uploadLimit' => '0',
            'source' => 'form_gallery',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'video'],
            ]),
        ];

        $firstLocation = $this->createMock(RemoteResourceLocation::class);
        $secondLocation = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => in_array($entry['remoteId'], ['remote-1', 'remote-2'], true)
                && $entry['source'] === 'form_gallery'))
            ->willReturnOnConsecutiveCalls($firstLocation, $secondLocation);

        $result = $this->transformer->reverseTransform($payload);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertSame([$firstLocation, $secondLocation], $result->toArray());
    }

    public function testRootIndexedInputIsIgnoredByReverseTransform(): void
    {
        $payload = [
            'uploadLimit' => '0',
            'source' => 'form_gallery',
            [
                'locationId' => '101',
                'remoteId' => 'remote-1',
                'type' => 'image',
            ],
            [
                'locationId' => '102',
                'remoteId' => 'remote-2',
                'type' => 'video',
            ],
        ];

        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $result = $this->transformer->reverseTransform($payload);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    public function testLimitOneStillReturnsCollection(): void
    {
        $payload = [
            'uploadLimit' => '1',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
            ]),
        ];

        $location = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::once())
            ->method('reverseTransform')
            ->willReturn($location);

        $result = $this->transformer->reverseTransform($payload);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertSame([$location], $result->toArray());
    }

    public function testOverLimitCollectionIsRejected(): void
    {
        $payload = [
            'uploadLimit' => '1',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'image'],
            ]),
        ];

        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform($payload);
    }

    public function testCollectionAtUploadLimitIsAccepted(): void
    {
        $payload = [
            'uploadLimit' => '2',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'image'],
            ]),
        ];

        $firstLocation = $this->createMock(RemoteResourceLocation::class);
        $secondLocation = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnOnConsecutiveCalls($firstLocation, $secondLocation);

        $result = $this->transformer->reverseTransform($payload);

        self::assertSame([$firstLocation, $secondLocation], $result->toArray());
    }

    public function testMalformedCollectionPayloadIsRejected(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform([
            'uploadLimit' => '0',
            'collectionPayload' => '{"remoteId":',
        ]);
    }

    public function testEmptyDataReturnsEmptyCollection(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $result = $this->transformer->reverseTransform([
            'uploadLimit' => '0',
            'collectionPayload' => '',
        ]);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    public function testNonArrayInputReturnsEmptyCollection(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('reverseTransform');

        $result = $this->transformer->reverseTransform('not an array');

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    public function testEntriesFailingInnerTransformationAreSkipped(): void
    {
        $location = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnOnConsecutiveCalls(null, $location);

        $result = $this->transformer->reverseTransform([
            'uploadLimit' => '0',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'image'],
            ]),
        ]);

        self::assertSame([$location], $result->toArray());
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
            'uploadLimit' => '0',
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
        self::assertSame('updated-source', $existingLocation->getSource());
        self::assertSame('new-source', $result[1]->getSource());
    }

    public function testOverridesAreCarriedThroughCollectionPayload(): void
    {
        $firstResource = new RemoteResource(
            'upload|image|first.jpg',
            RemoteResource::TYPE_IMAGE,
            'https://example.test/first.jpg',
            'md5-first',
        );
        $secondResource = new RemoteResource(
            'upload|image|second.jpg',
            RemoteResource::TYPE_IMAGE,
            'https://example.test/second.jpg',
            'md5-second',
        );
        $firstLocation = new RemoteResourceLocation(
            $firstResource,
            'gallery',
            id: 10,
            altTextOverride: 'Old local alt',
            captionOverride: 'Old local caption',
        );
        $secondLocation = new RemoteResourceLocation(
            $secondResource,
            'gallery',
            id: 11,
            altTextOverride: 'Kept local alt',
            captionOverride: 'Kept local caption',
        );

        $provider = $this->createMock(ProviderInterface::class);
        $provider
            ->method('loadByRemoteId')
            ->willReturnCallback(static fn (string $remoteId): RemoteResource => match ($remoteId) {
                'upload|image|first.jpg' => $firstResource,
                'upload|image|second.jpg' => $secondResource,
            });
        $provider
            ->method('loadLocation')
            ->willReturnCallback(static fn (int $id): RemoteResourceLocation => match ($id) {
                10 => $firstLocation,
                11 => $secondLocation,
            });
        $provider
            ->expects(self::never())
            ->method('updateOnRemote');

        $transformer = new RemoteMediaCollectionTransformer(
            new RemoteMediaTransformer($provider, new RemoteResourceService($provider)),
        );

        $viewData = $transformer->transform(new ArrayCollection([$firstLocation, $secondLocation]));
        $entries = json_decode($viewData['collectionPayload'], true);

        self::assertSame('Old local alt', $entries[0]['altTextOverride']);
        self::assertSame('Old local caption', $entries[0]['captionOverride']);
        self::assertSame('Kept local alt', $entries[1]['altTextOverride']);
        self::assertSame('Kept local caption', $entries[1]['captionOverride']);
        self::assertSame('Old local alt', $viewData['altTextOverride']);
        self::assertSame('Old local caption', $viewData['captionOverride']);

        $entries[0]['altTextOverride'] = 'New local alt';
        $entries[0]['captionOverride'] = '';

        $result = $transformer->reverseTransform([
            'uploadLimit' => '0',
            'collectionPayload' => json_encode($entries),
        ]);

        self::assertSame([$firstLocation, $secondLocation], $result->toArray());
        self::assertSame('New local alt', $firstLocation->getAltTextOverride());
        self::assertNull($firstLocation->getCaptionOverride());
        self::assertSame('Kept local alt', $secondLocation->getAltTextOverride());
        self::assertSame('Kept local caption', $secondLocation->getCaptionOverride());
    }
}
