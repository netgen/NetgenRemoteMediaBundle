<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Netgen\RemoteMedia\Form\Type\RemoteMediaCollectionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function in_array;
use function json_encode;

#[CoversClass(RemoteMediaCollectionType::class)]
class RemoteMediaCollectionTypeTest extends TypeTestCase
{
    private DataTransformerInterface|MockObject $innerTransformerMock;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->innerTransformerMock = $this->createMock(DataTransformerInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);

        parent::setUp();
    }

    #[DataProvider('uploadLimitOptionsDataProvider')]
    public function testUploadLimitOption(array $options, ?int $expectedViewValue, string $expectedSubmittedValue): void
    {
        $this->mockProviderFacets();

        $form = $this->factory->create(RemoteMediaCollectionType::class, null, $options);
        $view = $form->createView();

        self::assertSame($expectedViewValue, $view->vars['upload_limit']);
        self::assertTrue($view->vars['is_collection']);
        self::assertSame($expectedSubmittedValue, $view->children['uploadLimit']->vars['value']);
    }

    #[DataProvider('invalidUploadLimitOptionsDataProvider')]
    public function testInvalidUploadLimitOption(mixed $uploadLimit): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(RemoteMediaCollectionType::class, null, [
            'upload_limit' => $uploadLimit,
        ]);
    }

    public function testSubmitPayloadReturnsOrderedCollection(): void
    {
        $firstLocation = new RemoteResourceLocation(
            new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1'),
        );
        $secondLocation = new RemoteResourceLocation(
            new RemoteResource('remote-2', RemoteResource::TYPE_IMAGE, 'https://example.test/2', 'md5-2'),
        );

        $this->innerTransformerMock
            ->expects(self::exactly(2))
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => in_array($entry['remoteId'], ['remote-1', 'remote-2'], true)))
            ->willReturnOnConsecutiveCalls($firstLocation, $secondLocation);

        $form = $this->factory->create(RemoteMediaCollectionType::class);

        $form->submit([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'video'],
            ]),
            'source' => 'form_gallery',
        ]);

        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertSame([$firstLocation, $secondLocation], $data->toArray());
    }

    #[DataProvider('emptyPayloadDataProvider')]
    public function testSubmitEmptyPayloadWithPresetDataClearsCollection(array $submittedData): void
    {
        $firstLocation = new RemoteResourceLocation(
            new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1'),
        );
        $secondLocation = new RemoteResourceLocation(
            new RemoteResource('remote-2', RemoteResource::TYPE_IMAGE, 'https://example.test/2', 'md5-2'),
        );

        $this->innerTransformerMock
            ->expects(self::never())
            ->method('reverseTransform');

        $form = $this->factory->create(
            RemoteMediaCollectionType::class,
            new ArrayCollection([$firstLocation, $secondLocation]),
        );

        $form->submit($submittedData);

        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertTrue($data->isEmpty());
    }

    public function testSubmitMalformedPayloadIsNotSynchronized(): void
    {
        $form = $this->factory->create(RemoteMediaCollectionType::class);

        $form->submit([
            'collectionPayload' => '{"remoteId":',
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isSynchronized());
    }

    public function testSubmitRootIndexedFallbackIsConvertedToPayload(): void
    {
        $location = new RemoteResourceLocation(
            new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1'),
        );

        $this->innerTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => $entry['remoteId'] === 'remote-1'))
            ->willReturn($location);

        $form = $this->factory->create(RemoteMediaCollectionType::class);

        $form->submit([
            [
                'remoteId' => 'remote-1',
                'type' => 'image',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame([$location], $form->getData()->toArray());
    }

    public function testSubmitUnknownLocationIdCreatesFreshLocation(): void
    {
        $existingLocation = new RemoteResourceLocation(
            new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1'),
            id: 10,
        );
        $resultLocation = new RemoteResourceLocation(
            new RemoteResource('remote-2', RemoteResource::TYPE_IMAGE, 'https://example.test/2', 'md5-2'),
        );

        $this->innerTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $entry): bool => $entry['locationId'] === null))
            ->willReturn($resultLocation);

        $form = $this->factory->create(
            RemoteMediaCollectionType::class,
            new ArrayCollection([$existingLocation]),
        );

        $form->submit([
            'collectionPayload' => json_encode([
                [
                    'locationId' => '999',
                    'remoteId' => 'remote-2',
                    'type' => 'image',
                ],
            ]),
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame([$resultLocation], $form->getData()->toArray());
    }

    public function testSubmitOverLimitPayloadIsRejected(): void
    {
        $form = $this->factory->create(RemoteMediaCollectionType::class, null, [
            'upload_limit' => 1,
        ]);

        $form->submit([
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'image'],
            ]),
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isSynchronized());
    }

    public function testSubmittedUploadLimitCannotOverrideServerLimit(): void
    {
        $form = $this->factory->create(RemoteMediaCollectionType::class, null, [
            'upload_limit' => 1,
        ]);

        $form->submit([
            'uploadLimit' => '0',
            'collectionPayload' => json_encode([
                ['remoteId' => 'remote-1', 'type' => 'image'],
                ['remoteId' => 'remote-2', 'type' => 'image'],
            ]),
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isSynchronized());
    }

    public function testSubmitEmptyDataReturnsEmptyCollection(): void
    {
        $form = $this->factory->create(RemoteMediaCollectionType::class);

        $form->submit([]);

        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertTrue($data->isEmpty());
    }

    public function testBuildViewExposesExistingRemoteMediaLocations(): void
    {
        $locations = new ArrayCollection([
            new RemoteResourceLocation(new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1')),
            new RemoteResourceLocation(new RemoteResource('remote-2', RemoteResource::TYPE_IMAGE, 'https://example.test/2', 'md5-2')),
        ]);

        $this->mockProviderFacets();

        $form = $this->factory->create(RemoteMediaCollectionType::class, $locations);
        $view = $form->createView();

        self::assertSame($locations->toArray(), $view->vars['remote_media_locations']);
        self::assertNull($view->vars['upload_limit']);
        self::assertTrue($view->vars['is_collection']);
    }

    public function testBlockPrefixReusesRemoteMediaTheme(): void
    {
        $form = $this->factory->create(RemoteMediaCollectionType::class);

        self::assertContains('remote_media', $form->createView()->vars['block_prefixes']);
    }

    public static function uploadLimitOptionsDataProvider(): iterable
    {
        return [
            'default unlimited' => [
                [],
                null,
                '0',
            ],
            'null unlimited' => [
                ['upload_limit' => null],
                null,
                '0',
            ],
            'zero compatibility unlimited' => [
                ['upload_limit' => 0],
                null,
                '0',
            ],
            'fixed positive limit' => [
                ['upload_limit' => 3],
                3,
                '3',
            ],
        ];
    }

    public static function invalidUploadLimitOptionsDataProvider(): iterable
    {
        return [
            'negative' => [-1],
            'string' => ['invalid'],
        ];
    }

    public static function emptyPayloadDataProvider(): iterable
    {
        return [
            'empty payload' => [
                ['collectionPayload' => ''],
            ],
            'omitted payload' => [
                [],
            ],
        ];
    }

    protected function getExtensions(): array
    {
        $type = new RemoteMediaCollectionType(
            new RemoteMediaCollectionTransformer($this->innerTransformerMock),
            $this->providerMock,
        );

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    private function mockProviderFacets(): void
    {
        $this->providerMock
            ->method('getSupportedVisibilities')
            ->willReturn(RemoteResource::SUPPORTED_VISIBILITIES);

        $this->providerMock
            ->method('getSupportedTypes')
            ->willReturn(RemoteResource::SUPPORTED_TYPES);

        $this->providerMock
            ->method('listTags')
            ->willReturn([]);
    }
}
