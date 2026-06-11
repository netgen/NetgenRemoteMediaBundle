<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function in_array;
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

    public function testTransformsCollectionToEntryList(): void
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
                ['remoteId' => 'remote-1'],
                ['remoteId' => 'remote-2'],
            ],
            $this->transformer->transform(new ArrayCollection([$firstLocation, $secondLocation])),
        );
    }

    public function testTransformsNullToEmptyList(): void
    {
        $this->innerTransformer
            ->expects(self::never())
            ->method('transform');

        self::assertSame([], $this->transformer->transform(null));
    }

    public function testTransformSkipsNonLocationItems(): void
    {
        $location = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::once())
            ->method('transform')
            ->willReturn(['remoteId' => 'remote-1']);

        self::assertSame(
            [['remoteId' => 'remote-1']],
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

    public function testRootIndexedInputReturnsOrderedCollection(): void
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

        $firstLocation = $this->createMock(RemoteResourceLocation::class);
        $secondLocation = $this->createMock(RemoteResourceLocation::class);

        $this->innerTransformer
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => $entry['source'] === 'form_gallery'))
            ->willReturnOnConsecutiveCalls($firstLocation, $secondLocation);

        $result = $this->transformer->reverseTransform($payload);

        self::assertInstanceOf(ArrayCollection::class, $result);
        self::assertSame([$firstLocation, $secondLocation], $result->toArray());
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
}
