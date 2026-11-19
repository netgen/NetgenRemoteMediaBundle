<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaTransformer;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use Netgen\RemoteMedia\Tests\Form\Type\Fixtures\Gallery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

use function array_column;
use function array_keys;
use function array_map;
use function iterator_to_array;
use function json_decode;
use function json_encode;

#[CoversClass(RemoteMediaType::class)]
final class RemoteMediaTypeTest extends TypeTestCase
{
    private MockObject&ProviderInterface $providerMock;

    /**
     * Resources known to the provider, by remote ID.
     *
     * @var array<string, RemoteResource>
     */
    private array $resources = [];

    /**
     * Locations stored in the provider, by ID.
     *
     * @var array<int, RemoteResourceLocation>
     */
    private array $locations = [];

    private Translator $translator;

    private int $loadFromRemoteCalls = 0;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->providerMock
            ->method('loadByRemoteId')
            ->willReturnCallback(
                fn (string $remoteId): RemoteResource => $this->resources[$remoteId] ?? throw new RemoteResourceNotFoundException($remoteId),
            );

        $this->providerMock
            ->method('loadFromRemote')
            ->willReturnCallback(
                function (string $remoteId): RemoteResource {
                    ++$this->loadFromRemoteCalls;

                    throw new RemoteResourceNotFoundException($remoteId);
                },
            );

        $this->providerMock
            ->method('loadLocation')
            ->willReturnCallback(
                fn (int $id): RemoteResourceLocation => $this->locations[$id] ?? throw new RemoteResourceLocationNotFoundException($id),
            );

        $this->providerMock
            ->method('getSupportedVisibilities')
            ->willReturn(RemoteResource::SUPPORTED_VISIBILITIES);

        $this->providerMock
            ->method('getSupportedTypes')
            ->willReturn(RemoteResource::SUPPORTED_TYPES);

        $this->providerMock
            ->method('listTags')
            ->willReturn(['tag1', 'tag3']);

        parent::setUp();
    }

    #[DataProvider('uploadLimitDataProvider')]
    public function testUploadLimitOnlyChangesTheViewVariable(array $options, ?int $expectedUploadLimit): void
    {
        $form = $this->factory->create(RemoteMediaType::class, null, $options);
        $view = $form->createView();

        self::assertSame($expectedUploadLimit, $view->vars['upload_limit']);
        self::assertSame(['collectionPayload'], array_keys($view->children));
        self::assertSame('[]', $view->children['collectionPayload']->vars['value']);
        self::assertInstanceOf(ArrayCollection::class, $form->submit(['collectionPayload' => '[]'])->getData());
    }

    #[DataProvider('invalidUploadLimitDataProvider')]
    public function testInvalidUploadLimitOption(mixed $uploadLimit): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(RemoteMediaType::class, null, ['upload_limit' => $uploadLimit]);
    }

    #[DataProvider('viewOptionsDataProvider')]
    public function testBuildViewExposesOptions(array $options, array $expectedVars): void
    {
        $view = $this->factory->createNamed('gallery', RemoteMediaType::class, null, $options)->createView();

        foreach ($expectedVars as $name => $expectedValue) {
            self::assertSame($expectedValue, $view->vars[$name], $name);
        }
    }

    public function testViewHoldsTheCurrentValueAsPayloadOrderedByPosition(): void
    {
        $first = $this->storeLocation(6, 'upload|image|first.jpg', position: 0);
        $second = $this->storeLocation(7, 'upload|image|second.jpg', position: 1);
        $second->setAltTextOverride('Local alt')->setCropSettings([new CropSettings('hero', 1, 2, 300, 200)]);

        $view = $this->factory->create(RemoteMediaType::class, new ArrayCollection([$second, $first]))->createView();

        self::assertSame([$first, $second], $view->vars['remote_media_locations']);

        $entries = json_decode($view->children['collectionPayload']->vars['value'], true);

        self::assertSame([6, 7], array_column($entries, 'locationId'));
        self::assertSame(['upload|image|first.jpg', 'upload|image|second.jpg'], array_column($entries, 'remoteId'));
        self::assertSame('Local alt', $entries[1]['altTextOverride']);
        self::assertSame('{"hero":{"x":1,"y":2,"w":300,"h":200}}', $entries[1]['cropSettings']);
    }

    public function testSettingSingleLocationAsDataIsRejected(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('The data of RemoteMediaType is a collection, also when "upload_limit" is 1.');

        $this->factory->create(RemoteMediaType::class, $this->storeLocation(6, 'upload|image|first.jpg'), ['upload_limit' => 1]);
    }

    public function testSubmitReturnsCollectionInSubmittedOrder(): void
    {
        $this->addResource('upload|image|first.jpg');
        $this->addResource('upload|video|second.mp4');

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('updateOnRemote');

        $form = $this->factory->createNamed('gallery', RemoteMediaType::class);

        $form->submit([
            'collectionPayload' => json_encode([
                [
                    'remoteId' => 'upload|video|second.mp4',
                    'altText' => 'Second alt',
                    'altTextOverride' => 'Second local alt',
                    'captionOverride' => 'Second local caption',
                    'watermarkText' => 'Watermark',
                    'cropSettings' => '{"hero":{"x":10,"y":20,"w":1920,"h":1080}}',
                    'source' => 'own_source',
                ],
                [
                    'remoteId' => 'upload|image|first.jpg',
                    'caption' => 'First caption',
                    'tags' => ['b', 'a'],
                ],
            ]),
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertCount(2, $data);

        AbstractTestCase::assertRemoteResourceLocationSame(
            new RemoteResourceLocation(
                remoteResource: new RemoteResource(
                    remoteId: 'upload|video|second.mp4',
                    type: 'image',
                    url: 'https://example.test/upload|video|second.mp4',
                    md5: 'md5',
                    altText: 'Second alt',
                ),
                source: 'own_source',
                cropSettings: [new CropSettings('hero', 10, 20, 1920, 1080)],
                watermarkText: 'Watermark',
                altTextOverride: 'Second local alt',
                captionOverride: 'Second local caption',
                position: 0,
            ),
            $data[0],
        );
        self::assertNull($data[0]->getId());

        AbstractTestCase::assertRemoteResourceLocationSame(
            new RemoteResourceLocation(
                remoteResource: new RemoteResource(
                    remoteId: 'upload|image|first.jpg',
                    type: 'image',
                    url: 'https://example.test/upload|image|first.jpg',
                    md5: 'md5',
                    caption: 'First caption',
                    tags: ['a', 'b'],
                ),
                source: 'form_gallery',
                position: 1,
            ),
            $data[1],
        );
    }

    public function testSubmitKeepsOwnLocation(): void
    {
        $location = $this->storeLocation(5, 'upload|image|owned.jpg');
        $location->setWatermarkText('Old watermark');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection([$location]));

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => '5', 'remoteId' => 'upload|image|owned.jpg', 'watermarkText' => 'New watermark'],
            ]),
        ]);

        self::assertTrue($form->isValid());
        self::assertSame([$location], $form->getData()->toArray());
        self::assertSame(5, $form->getData()->first()->getId());
        self::assertSame('New watermark', $location->getWatermarkText());
    }

    public function testSubmitForeignLocationIdCreatesNewLocation(): void
    {
        $ownLocation = $this->storeLocation(5, 'upload|image|owned.jpg');
        $foreignLocation = $this->storeLocation(999, 'upload|image|foreign.jpg');
        $foreignLocation->setWatermarkText('Belongs to another entity');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection([$ownLocation]));

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 999, 'remoteId' => 'upload|image|foreign.jpg', 'watermarkText' => 'Taken over'],
            ]),
        ]);

        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertCount(1, $data);
        self::assertNotSame($foreignLocation, $data[0]);
        self::assertNull($data[0]->getId());
        self::assertSame($foreignLocation->getRemoteResource(), $data[0]->getRemoteResource());
        self::assertSame('Taken over', $data[0]->getWatermarkText());
        self::assertSame('Belongs to another entity', $foreignLocation->getWatermarkText());
    }

    public function testSubmitReordersAndRemovesStoredLocations(): void
    {
        $locations = $this->storeGallery();

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 8, 'remoteId' => 'upload|image|8.jpg'],
                ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg'],
            ]),
        ]);

        self::assertTrue($form->isValid());
        self::assertSame([$locations[2], $locations[0]], $form->getData()->toArray());
        self::assertSame(0, $locations[2]->getPosition());
        self::assertSame(1, $locations[0]->getPosition());

        self::assertSame([$locations[2], $locations[0]], $form->createView()->vars['remote_media_locations']);
    }

    /**
     * The overrides are stored on the locations. Cloud values sent back as
     * they were loaded don't update the remote.
     */
    public function testSubmitStoresOverridesWithoutUpdatingTheRemote(): void
    {
        $locations = $this->storeGallery();
        $locations[0]->getRemoteResource()->setAltText('Cloud alt')->setCaption('Cloud caption');
        $locations[0]->setAltTextOverride('Old local alt')->setCaptionOverride('Old local caption');
        $locations[1]->setAltTextOverride('Kept local alt');

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit([
            'collectionPayload' => json_encode([
                [
                    'locationId' => 6,
                    'remoteId' => 'upload|image|6.jpg',
                    'altText' => 'Cloud alt',
                    'caption' => 'Cloud caption',
                    'altTextOverride' => 'New local alt',
                    'captionOverride' => '',
                ],
                ['locationId' => 7, 'remoteId' => 'upload|image|7.jpg', 'altTextOverride' => 'Kept local alt', 'captionOverride' => ''],
                ['locationId' => 8, 'remoteId' => 'upload|image|8.jpg', 'altTextOverride' => '', 'captionOverride' => 'New local caption'],
            ]),
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame($locations, $form->getData()->toArray());

        self::assertSame('New local alt', $locations[0]->getAltTextOverride());
        self::assertNull($locations[0]->getCaptionOverride());
        self::assertSame('Cloud alt', $locations[0]->getRemoteResource()->getAltText());
        self::assertSame('Cloud caption', $locations[0]->getRemoteResource()->getCaption());
        self::assertSame('New local alt', $locations[0]->getAltText());
        self::assertSame('Cloud caption', $locations[0]->getCaption());

        self::assertSame('Kept local alt', $locations[1]->getAltTextOverride());
        self::assertNull($locations[1]->getCaptionOverride());

        self::assertNull($locations[2]->getAltTextOverride());
        self::assertSame('New local caption', $locations[2]->getCaptionOverride());
    }

    #[DataProvider('missingPayloadDataProvider')]
    public function testSubmitWithoutPayloadDoesNotChangeTheField(mixed $submittedData): void
    {
        $locations = $this->storeGallery();
        $locations[1]->setAltTextOverride('Local alt')->setCropSettings([new CropSettings('hero', 1, 2, 300, 200)]);

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit($submittedData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertSame($locations, $data->toArray());
        self::assertSame('Local alt', $locations[1]->getAltTextOverride());
        AbstractTestCase::assertCropSettingsSame(new CropSettings('hero', 1, 2, 300, 200), $locations[1]->getCropSettings()[0]);
    }

    #[DataProvider('missingPayloadDataProvider')]
    public function testSubmitWithoutPayloadOnEmptyFieldReturnsEmptyCollection(mixed $submittedData): void
    {
        $form = $this->factory->create(RemoteMediaType::class);

        $form->submit($submittedData);

        self::assertTrue($form->isValid());
        self::assertInstanceOf(ArrayCollection::class, $form->getData());
        self::assertTrue($form->getData()->isEmpty());
    }

    public function testSubmitEmptyListClearsTheField(): void
    {
        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($this->storeGallery()));

        $form->submit(['collectionPayload' => '[]']);

        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertTrue($data->isEmpty());
        self::assertSame([], $form->createView()->vars['remote_media_locations']);
    }

    #[DataProvider('invalidSubmissionDataProvider')]
    public function testInvalidSubmissionIsRejected(mixed $submittedData): void
    {
        $locations = $this->storeGallery();

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit($submittedData);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
        self::assertNull($locations[0]->getWatermarkText());
        self::assertSame([], $locations[0]->getCropSettings());
    }

    public function testSubmitKeepsOnlyTheFirstEntryOfARepeatedLocation(): void
    {
        $locations = $this->storeGallery();

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'watermarkText' => 'first'],
                ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'watermarkText' => 'repeated'],
            ]),
        ]);

        self::assertTrue($form->isValid());
        self::assertSame([$locations[0]], $form->getData()->toArray());
        self::assertSame('first', $locations[0]->getWatermarkText());
    }

    public function testLimitOneReturnsCollectionWithOneLocation(): void
    {
        $this->addResource('upload|image|first.jpg');

        $form = $this->factory->create(RemoteMediaType::class, null, ['upload_limit' => 1]);

        $form->submit([
            'collectionPayload' => json_encode([['remoteId' => 'upload|image|first.jpg']]),
        ]);

        self::assertTrue($form->isValid());

        $data = $form->getData();
        self::assertInstanceOf(ArrayCollection::class, $data);
        self::assertCount(1, $data);
        self::assertSame('upload|image|first.jpg', $data[0]->getRemoteResource()->getRemoteId());
    }

    public function testLimitOneReturnsEmptyCollectionWhenEmptied(): void
    {
        $location = $this->storeLocation(6, 'upload|image|first.jpg');

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection([$location]), ['upload_limit' => 1]);

        $form->submit(['collectionPayload' => '[]']);

        self::assertTrue($form->isValid());
        self::assertInstanceOf(ArrayCollection::class, $form->getData());
        self::assertTrue($form->getData()->isEmpty());
    }

    public function testSameResourceInTwoFieldsKeepsTheCloudValuesChangedInOne(): void
    {
        $resource = $this->addResource('upload|image|shared.jpg');
        $resource->setCaption('Stored caption');

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->with($resource);

        $parentForm = $this->factory->createBuilder()
            ->add('image', RemoteMediaType::class, ['upload_limit' => 1])
            ->add('gallery', RemoteMediaType::class)
            ->getForm();

        $loaded = ['altText' => '', 'caption' => 'Stored caption', 'tags' => []];

        // The alt text is changed in the first field only, the second one posts the values it loaded.
        $parentForm->submit([
            'image' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|shared.jpg', 'altText' => 'New alt', 'caption' => 'Stored caption', 'loaded' => $loaded],
                ]),
            ],
            'gallery' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|shared.jpg', 'altText' => '', 'caption' => 'Stored caption', 'loaded' => $loaded],
                ]),
            ],
        ]);

        self::assertTrue($parentForm->isValid());
        self::assertSame('New alt', $resource->getAltText());
        self::assertSame('Stored caption', $resource->getCaption());
    }

    public function testSameResourceInTwoFieldsKeepsTheCloudValuesChangedInEach(): void
    {
        $resource = $this->addResource('upload|image|shared.jpg');

        $parentForm = $this->factory->createBuilder()
            ->add('image', RemoteMediaType::class, ['upload_limit' => 1])
            ->add('gallery', RemoteMediaType::class)
            ->getForm();

        $parentForm->submit([
            'image' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|shared.jpg', 'altText' => 'New alt', 'loaded' => []],
                ]),
            ],
            'gallery' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|shared.jpg', 'caption' => 'New caption', 'tags' => ['new'], 'loaded' => []],
                ]),
            ],
        ]);

        self::assertTrue($parentForm->isValid());
        self::assertSame('New alt', $resource->getAltText());
        self::assertSame('New caption', $resource->getCaption());
        self::assertSame(['new'], $resource->getTags());
    }

    public function testEntryWithoutLoadedValuesWritesItsCloudValues(): void
    {
        $resource = $this->addResource('upload|image|shared.jpg');
        $resource->setAltText('Stored alt');

        $form = $this->factory->create(RemoteMediaType::class);

        $form->submit([
            'collectionPayload' => json_encode([
                ['remoteId' => 'upload|image|shared.jpg', 'altText' => ''],
            ]),
        ]);

        self::assertTrue($form->isValid());
        self::assertNull($resource->getAltText());
    }

    #[DataProvider('limitExceededMessageDataProvider')]
    public function testSubmitOverLimitIsInvalidWithTranslatedMessage(string $locale, string $expectedMessage): void
    {
        $this->translator->setLocale($locale);

        $this->storeGallery();

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote');

        $parentForm = $this->factory->createBuilder()
            ->add('gallery', RemoteMediaType::class, ['upload_limit' => 2])
            ->getForm();

        $parentForm->submit([
            'gallery' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|6.jpg'],
                    ['remoteId' => 'upload|image|7.jpg'],
                    ['remoteId' => 'upload|image|8.jpg'],
                ]),
            ],
        ]);

        $form = $parentForm->get('gallery');

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertFalse($parentForm->isValid());
        // The error stays on the field instead of bubbling up to the parent form.
        self::assertSame([$expectedMessage], self::errorMessages($form));
        self::assertCount(0, $parentForm->getErrors());
    }

    public function testBuildViewAfterRejectedSubmissionShowsWhatWasSubmitted(): void
    {
        $locations = $this->storeGallery();
        $locations[0]->getRemoteResource()->setAltText('Stored alt');
        $this->addResource('upload|image|new.jpg');

        $form = $this->factory->createNamed('gallery', RemoteMediaType::class, new ArrayCollection($locations), ['upload_limit' => 3]);

        // Reordered, with a new resource, a crop and edited texts, but one resource over the limit.
        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 8, 'remoteId' => 'upload|image|8.jpg', 'cropSettings' => '{"hero":{"x":5,"y":6,"w":70,"h":80}}'],
                ['remoteId' => 'upload|image|new.jpg', 'watermarkText' => 'New watermark'],
                [
                    'locationId' => 6,
                    'remoteId' => 'upload|image|6.jpg',
                    'altText' => 'Edited alt',
                    'caption' => 'Edited caption',
                    'tags' => ['edited'],
                    'altTextOverride' => 'Edited local alt',
                    'captionOverride' => 'Edited local caption',
                    'loaded' => ['altText' => 'Stored alt'],
                ],
                ['locationId' => 7, 'remoteId' => 'upload|image|7.jpg'],
            ]),
        ]);

        self::assertFalse($form->isValid());

        $view = $form->createView();
        $viewLocations = $view->vars['remote_media_locations'];

        self::assertSame(
            ['upload|image|8.jpg', 'upload|image|new.jpg', 'upload|image|6.jpg', 'upload|image|7.jpg'],
            array_map(static fn (RemoteResourceLocation $location): string => $location->getRemoteResource()->getRemoteId(), $viewLocations),
        );
        self::assertSame([8, null, 6, 7], array_map(static fn (RemoteResourceLocation $location): ?int => $location->getId(), $viewLocations));

        AbstractTestCase::assertCropSettingsSame(new CropSettings('hero', 5, 6, 70, 80), $viewLocations[0]->getCropSettings()[0]);
        self::assertSame('New watermark', $viewLocations[1]->getWatermarkText());
        self::assertSame('form_gallery', $viewLocations[1]->getSource());
        self::assertSame('Edited alt', $viewLocations[2]->getRemoteResource()->getAltText());
        self::assertSame('Edited caption', $viewLocations[2]->getRemoteResource()->getCaption());
        self::assertSame(['edited'], $viewLocations[2]->getRemoteResource()->getTags());
        self::assertSame('Edited local alt', $viewLocations[2]->getAltTextOverride());
        self::assertSame('Edited local caption', $viewLocations[2]->getCaptionOverride());

        // The values the widget loaded go back with it, so a resubmit still sees the edits as changes.
        self::assertSame(
            [null, null, ['altText' => 'Stored alt', 'caption' => null, 'tags' => []], null],
            $view->vars['remote_media_loaded_values'],
        );

        // The same payload is rendered into the hidden input.
        self::assertSame(
            [8, null, 6, 7],
            array_column(json_decode($view->children['collectionPayload']->vars['value'], true), 'locationId'),
        );

        // Nothing of the rejected submission reached the stored locations and resources.
        self::assertNotSame($locations[0], $viewLocations[2]);
        self::assertNotSame($locations[0]->getRemoteResource(), $viewLocations[2]->getRemoteResource());
        self::assertSame('Stored alt', $locations[0]->getRemoteResource()->getAltText());
        self::assertNull($locations[0]->getAltTextOverride());
        self::assertSame([], $locations[2]->getCropSettings());
        self::assertSame([0, 1, 2], array_map(static fn (RemoteResourceLocation $location): ?int => $location->getPosition(), $locations));
    }

    public function testBuildViewOfValidFieldInRejectedFormKeepsTheLoadedValuesFromThePayload(): void
    {
        $location = $this->storeLocation(6, 'upload|image|6.jpg', position: 0);
        $location->getRemoteResource()->setAltText('Stored alt');
        $this->addResource('upload|image|7.jpg');
        $this->addResource('upload|image|8.jpg');

        $parentForm = $this->factory->createBuilder(FormType::class, ['image' => new ArrayCollection([$location]), 'gallery' => new ArrayCollection()])
            ->add('image', RemoteMediaType::class, ['upload_limit' => 1])
            ->add('gallery', RemoteMediaType::class, ['upload_limit' => 1])
            ->getForm();

        $loaded = ['altText' => 'Stored alt', 'caption' => '', 'tags' => []];

        // The image field is valid, the gallery field is over its limit.
        $parentForm->submit([
            'image' => [
                'collectionPayload' => json_encode([
                    ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'altText' => 'Edited alt', 'loaded' => $loaded],
                ]),
            ],
            'gallery' => [
                'collectionPayload' => json_encode([
                    ['remoteId' => 'upload|image|7.jpg'],
                    ['remoteId' => 'upload|image|8.jpg'],
                ]),
            ],
        ]);

        self::assertFalse($parentForm->isValid());
        self::assertTrue($parentForm->get('image')->isSynchronized());

        $view = $parentForm->createView()->children['image'];

        self::assertSame('Edited alt', $view->vars['remote_media_locations'][0]->getRemoteResource()->getAltText());
        // The widget sends the edited alt text against the stored one again, so the next save still writes it.
        self::assertSame(
            [['altText' => 'Stored alt', 'caption' => null, 'tags' => []]],
            $view->vars['remote_media_loaded_values'],
        );
    }

    public function testBuildViewBeforeSubmissionHasNoLoadedValues(): void
    {
        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($this->storeGallery()));

        self::assertSame([], $form->createView()->vars['remote_media_loaded_values']);
    }

    public function testBuildViewAfterRejectedSubmissionSkipsWhatCannotBeShown(): void
    {
        $locations = $this->storeGallery();

        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($locations));

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 7, 'remoteId' => 'upload|image|7.jpg'],
                ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'cropSettings' => ['hero' => ['x' => 1]]],
                ['remoteId' => 'upload|image|unknown.jpg'],
            ]),
        ]);

        self::assertFalse($form->isValid());

        $viewLocations = $form->createView()->vars['remote_media_locations'];

        self::assertCount(1, $viewLocations);
        self::assertSame(7, $viewLocations[0]->getId());
    }

    public function testBuildViewAfterRejectedSubmissionDoesNotAskTheRemote(): void
    {
        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection(), ['upload_limit' => 1]);

        $form->submit([
            'collectionPayload' => json_encode([
                ['remoteId' => 'upload|image|unknown-1.jpg'],
                ['remoteId' => 'upload|image|unknown-2.jpg'],
                ['remoteId' => 'upload|image|unknown-3.jpg'],
            ]),
        ]);

        self::assertFalse($form->isValid());

        $callsBeforeView = $this->loadFromRemoteCalls;

        self::assertSame([], $form->createView()->vars['remote_media_locations']);
        self::assertSame($callsBeforeView, $this->loadFromRemoteCalls);
    }

    public function testBuildViewAfterUnreadableSubmissionShowsNothing(): void
    {
        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($this->storeGallery()));

        $form->submit(['collectionPayload' => '{"remoteId":']);

        self::assertFalse($form->isValid());

        $view = $form->createView();

        self::assertSame([], $view->vars['remote_media_locations']);
        self::assertSame('{"remoteId":', $view->children['collectionPayload']->vars['value']);
    }

    #[DataProvider('emptyDataDataProvider')]
    public function testFieldWithoutLocationsIsEmpty(?ArrayCollection $data): void
    {
        $form = $this->createParentForm($data);

        self::assertSame('[]', $form->get('media')->get('collectionPayload')->getData());
        self::assertTrue($form->get('media')->isEmpty());
        self::assertTrue($form->isEmpty());
    }

    public function testFieldWithLocationsIsNotEmpty(): void
    {
        $form = $this->createParentForm(new ArrayCollection($this->storeGallery()));

        self::assertFalse($form->get('media')->isEmpty());
        self::assertFalse($form->isEmpty());
    }

    #[DataProvider('emptySubmissionDataProvider')]
    public function testFieldIsEmptyAfterEmptySubmission(mixed $submittedData): void
    {
        $form = $this->factory->create(RemoteMediaType::class);

        $form->submit($submittedData);

        self::assertTrue($form->isValid());
        self::assertTrue($form->isEmpty());
    }

    public function testFieldIsEmptyAfterBeingCleared(): void
    {
        $form = $this->factory->create(RemoteMediaType::class, new ArrayCollection($this->storeGallery()));

        $form->submit(['collectionPayload' => '[]']);

        self::assertTrue($form->isValid());
        self::assertTrue($form->isEmpty());
    }

    public function testFieldIsNotEmptyAfterSubmittingLocations(): void
    {
        $this->addResource('upload|image|new.jpg');

        $form = $this->factory->create(RemoteMediaType::class);

        $form->submit(['collectionPayload' => json_encode([['remoteId' => 'upload|image|new.jpg']])]);

        self::assertTrue($form->isValid());
        self::assertFalse($form->isEmpty());
    }

    /**
     * An optional embedded form with a data class gets no object when it is
     * submitted empty (Symfony checks whether the form is empty).
     */
    #[DataProvider('emptySubmissionDataProvider')]
    public function testEmptyEmbeddedFormGetsNoObject(mixed $submittedData): void
    {
        $form = $this->createFormWithEmbeddedForm();

        $form->submit(['embedded' => ['images' => $submittedData]]);

        self::assertTrue($form->isValid());
        self::assertNull($form->get('embedded')->getData());
    }

    public function testEmbeddedFormWithLocationsGetsAnObject(): void
    {
        $this->addResource('upload|image|new.jpg');

        $form = $this->createFormWithEmbeddedForm();

        $form->submit(['embedded' => ['images' => ['collectionPayload' => json_encode([['remoteId' => 'upload|image|new.jpg']])]]]);

        self::assertTrue($form->isValid());

        $data = $form->get('embedded')->getData();
        self::assertInstanceOf(Gallery::class, $data);
        self::assertCount(1, $data->getImages());
    }

    public function testBlockPrefix(): void
    {
        self::assertContains('remote_media', $this->factory->create(RemoteMediaType::class)->createView()->vars['block_prefixes']);
    }

    public static function emptyDataDataProvider(): iterable
    {
        return [
            'no data' => [null],
            'empty collection' => [new ArrayCollection()],
        ];
    }

    public static function emptySubmissionDataProvider(): iterable
    {
        return [
            'empty list' => [['collectionPayload' => '[]']],
            'empty string' => [['collectionPayload' => '']],
            'null payload' => [['collectionPayload' => null]],
            'no payload' => [[]],
            'null' => [null],
        ];
    }

    public static function uploadLimitDataProvider(): iterable
    {
        return [
            'default is unlimited' => [[], null],
            'null is unlimited' => [['upload_limit' => null], null],
            'single resource' => [['upload_limit' => 1], 1],
            'several resources' => [['upload_limit' => 3], 3],
        ];
    }

    public static function invalidUploadLimitDataProvider(): iterable
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'string' => ['3'],
            'float' => [1.5],
        ];
    }

    public static function viewOptionsDataProvider(): iterable
    {
        return [
            'defaults' => [
                [],
                [
                    'variation_group' => 'default',
                    'allowed_variations' => [],
                    'allowed_visibilities' => [],
                    'allowed_types' => [],
                    'allowed_tags' => [],
                    'parent_folder' => null,
                    'folder' => null,
                    'upload_context' => [],
                    'disable_upload' => false,
                    'hide_filename' => false,
                    'upload_limit' => null,
                    'location_source' => 'form_gallery',
                    'remote_media_locations' => [],
                ],
            ],
            'folders as paths' => [
                [
                    'variation_group' => null,
                    'allowed_visibilities' => ['public', 'other'],
                    'allowed_types' => ['image', 'video', 'pdf'],
                    'allowed_tags' => ['tag1', 'tag2'],
                    'parent_folder' => 'media/test',
                    'folder' => 'media',
                    'upload_context' => ['test'],
                    'location_source' => 'product_gallery',
                ],
                [
                    'variation_group' => 'default',
                    'allowed_visibilities' => ['public'],
                    'allowed_types' => ['image', 'video'],
                    'allowed_tags' => ['tag1'],
                    'parent_folder' => ['id' => 'media/test', 'label' => 'test'],
                    'folder' => ['id' => 'media', 'label' => 'media'],
                    'upload_context' => [],
                    'location_source' => 'product_gallery',
                ],
            ],
            'folders as objects' => [
                [
                    'variation_group' => 'product_image',
                    'allowed_visibilities' => ['protected'],
                    'allowed_types' => ['other'],
                    'parent_folder' => Folder::fromPath('media/test'),
                    'folder' => Folder::fromPath('media'),
                    'upload_context' => [
                        'alt' => 'Some alt',
                        'caption' => 'Some caption',
                        'type' => 'product_image',
                    ],
                    'disable_upload' => true,
                    'hide_filename' => true,
                    'upload_limit' => 1,
                ],
                [
                    'variation_group' => 'product_image',
                    'allowed_visibilities' => ['protected'],
                    'allowed_types' => ['other'],
                    'allowed_tags' => [],
                    'parent_folder' => ['id' => 'media/test', 'label' => 'test'],
                    'folder' => ['id' => 'media', 'label' => 'media'],
                    'upload_context' => [
                        'alt' => 'Some alt',
                        'caption' => 'Some caption',
                        'type' => 'product_image',
                    ],
                    'disable_upload' => true,
                    'hide_filename' => true,
                    'upload_limit' => 1,
                ],
            ],
        ];
    }

    public static function missingPayloadDataProvider(): iterable
    {
        return [
            'field not submitted at all' => [null],
            'no payload' => [[]],
            'null payload' => [['collectionPayload' => null]],
            'empty payload' => [['collectionPayload' => '']],
        ];
    }

    public static function invalidSubmissionDataProvider(): iterable
    {
        return [
            'malformed JSON' => [
                ['collectionPayload' => '{"remoteId":'],
            ],
            'JSON object instead of a list' => [
                ['collectionPayload' => '{}'],
            ],
            'payload submitted as an array' => [
                ['collectionPayload' => [['remoteId' => 'upload|image|6.jpg']]],
            ],
            'scalar submitted for the field' => [
                '[]',
            ],
            'crop settings as an object' => [
                ['collectionPayload' => json_encode([
                    ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'cropSettings' => ['hero' => ['x' => 1, 'y' => 2, 'w' => 3, 'h' => 4]]],
                ])],
            ],
            'alt text as an object' => [
                ['collectionPayload' => json_encode([
                    ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'altText' => ['en' => 'Alt']],
                ])],
            ],
            'tags as a string' => [
                ['collectionPayload' => json_encode([
                    ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'tags' => 'one,two'],
                ])],
            ],
            'location ID as a list' => [
                ['collectionPayload' => json_encode([
                    ['locationId' => [6], 'remoteId' => 'upload|image|6.jpg'],
                ])],
            ],
            'fields of the single resource shape next to the payload' => [
                ['collectionPayload' => '[]', 'remoteId' => 'upload|image|6.jpg', 'locationId' => '6'],
            ],
        ];
    }

    public static function limitExceededMessageDataProvider(): iterable
    {
        return [
            ['en', 'Too many files selected (3). The maximum allowed is 2.'],
            ['hr', 'Odabrano je previše datoteka (3). Najveći dopušteni broj je 2.'],
            ['no', 'For mange filer er valgt (3). Maksimalt tillatt antall er 2.'],
        ];
    }

    protected function getExtensions(): array
    {
        $service = new RemoteResourceService($this->providerMock);
        $type = new RemoteMediaType(
            new RemoteMediaTransformer($this->providerMock, $service),
            $this->providerMock,
            $service,
        );

        // Form errors are translated with the translations shipped by the bundle.
        $this->translator = new Translator('en');
        $this->translator->addLoader('yaml', new YamlFileLoader());
        foreach (['en', 'hr', 'no'] as $locale) {
            $this->translator->addResource(
                'yaml',
                __DIR__ . '/../../../../bundle/Resources/translations/validators.' . $locale . '.yaml',
                $locale,
                'validators',
            );
        }

        $validator = Validation::createValidatorBuilder()
            ->setTranslator($this->translator)
            ->setTranslationDomain('validators')
            ->getValidator();

        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension($validator, false),
        ];
    }

    /**
     * A form without data, with "media" as its only field.
     *
     * @param ArrayCollection<int, RemoteResourceLocation>|null $data
     *
     * @return FormInterface<mixed>
     */
    private function createParentForm(?ArrayCollection $data): FormInterface
    {
        return $this->factory->createBuilder()
            ->add('media', RemoteMediaType::class, ['data' => $data])
            ->getForm();
    }

    /**
     * @return FormInterface<mixed>
     */
    private function createFormWithEmbeddedForm(): FormInterface
    {
        $builder = $this->factory->createBuilder();
        $builder->add(
            $builder->create('embedded', FormType::class, ['data_class' => Gallery::class, 'required' => false])
                ->add('images', RemoteMediaType::class),
        );

        return $builder->getForm();
    }

    private function addResource(string $remoteId): RemoteResource
    {
        return $this->resources[$remoteId] = new RemoteResource(
            remoteId: $remoteId,
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://example.test/' . $remoteId,
            md5: 'md5',
        );
    }

    private function storeLocation(int $id, string $remoteId, ?int $position = null): RemoteResourceLocation
    {
        return $this->locations[$id] = new RemoteResourceLocation(
            $this->addResource($remoteId),
            id: $id,
            position: $position,
        );
    }

    /**
     * @return array<int, RemoteResourceLocation> Stored locations 6, 7 and 8, in that order
     */
    private function storeGallery(): array
    {
        return [
            $this->storeLocation(6, 'upload|image|6.jpg', position: 0),
            $this->storeLocation(7, 'upload|image|7.jpg', position: 1),
            $this->storeLocation(8, 'upload|image|8.jpg', position: 2),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function errorMessages(FormInterface $form): array
    {
        $messages = [];
        foreach (iterator_to_array($form->getErrors(true), false) as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }
}
