<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Core\Factory\DateTime as DateTimeFactory;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaTransformer;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Netgen\RemoteMedia\Tests\Form\Type\Fixtures\Gallery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

use function array_map;
use function json_decode;
use function json_encode;
use function sys_get_temp_dir;

/**
 * Submits the form onto an entity stored with Doctrine, then flushes and
 * reloads the entity, with only the remote (Cloudinary) left out.
 */
#[CoversClass(RemoteMediaType::class)]
#[CoversClass(RemoteMediaCollectionTransformer::class)]
#[CoversClass(RemoteMediaTransformer::class)]
#[RequiresPhpExtension('pdo_sqlite')]
final class RemoteMediaTypePersistenceTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        $this->entityManager = self::createEntityManager();

        $provider = new CloudinaryProvider(
            new Registry(),
            new VariationResolver(new Registry(), null),
            $this->entityManager,
            $this->createMock(GatewayInterface::class),
            new DateTimeFactory(),
            new UploadOptionsResolver(new VisibilityTypeConverter(), CloudinaryProvider::FOLDER_MODE_DYNAMIC, false, false),
            [],
            [],
            CloudinaryProvider::FOLDER_MODE_DYNAMIC,
        );

        $service = new RemoteResourceService($provider);

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new RemoteMediaType(new RemoteMediaTransformer($provider, $service), $provider, $service))
            ->addExtension(new ValidatorExtension(Validation::createValidator(), false))
            ->getFormFactory();
    }

    public function testReorderRemovalAndAdditionSurviveFlushAndReload(): void
    {
        $galleryId = $this->storeGallery('upload|image|a.jpg', 'upload|image|b.jpg', 'upload|image|c.jpg');
        $this->storeResource('upload|image|d.jpg');

        $gallery = $this->loadGallery($galleryId);
        [$a, $b, $c] = self::locationIds($gallery);

        $form = $this->createForm($gallery);

        // What the widget renders from the stored gallery.
        self::assertSame(
            [$a, $b, $c],
            array_map(
                static fn (array $entry): int => $entry['locationId'],
                json_decode($form->createView()->children['images']->children['collectionPayload']->vars['value'], true),
            ),
        );

        // The editor moves the last image to the front, removes the middle one and adds a new one in between.
        $form->submit([
            'images' => [
                'collectionPayload' => json_encode([
                    ['locationId' => $c, 'remoteId' => 'upload|image|c.jpg', 'watermarkText' => 'Now first'],
                    ['remoteId' => 'upload|image|d.jpg', 'altTextOverride' => 'Added in between'],
                    ['locationId' => $a, 'remoteId' => 'upload|image|a.jpg'],
                ]),
            ],
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid(), (string) $form->getErrors(true));

        $this->entityManager->flush();

        $reloaded = $this->loadGallery($galleryId);

        self::assertSame(['upload|image|c.jpg', 'upload|image|d.jpg', 'upload|image|a.jpg'], self::remoteIds($reloaded));
        self::assertSame([0, 1, 2], self::positions($reloaded));

        $reloadedIds = self::locationIds($reloaded);
        self::assertSame($c, $reloadedIds[0]);
        self::assertSame($a, $reloadedIds[2]);
        self::assertNotContains($reloadedIds[1], [$a, $b, $c]);

        self::assertSame('Now first', $reloaded->getImages()[0]->getWatermarkText());
        self::assertSame('Added in between', $reloaded->getImages()[1]->getAltTextOverride());
        self::assertSame('form_images', $reloaded->getImages()[1]->getSource());

        // The location of the removed image is gone, its resource stays.
        self::assertNull($this->entityManager->find(RemoteResourceLocation::class, $b));
        self::assertNotNull(
            $this->entityManager->getRepository(RemoteResource::class)->findOneBy(['remoteId' => 'upload|image|b.jpg']),
        );

        // A second edit which only swaps the first two images.
        $form = $this->createForm($reloaded);
        $form->submit([
            'images' => [
                'collectionPayload' => json_encode([
                    ['locationId' => $reloadedIds[1], 'remoteId' => 'upload|image|d.jpg'],
                    ['locationId' => $reloadedIds[0], 'remoteId' => 'upload|image|c.jpg'],
                    ['locationId' => $reloadedIds[2], 'remoteId' => 'upload|image|a.jpg'],
                ]),
            ],
        ]);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));

        $this->entityManager->flush();

        $reloaded = $this->loadGallery($galleryId);

        self::assertSame(['upload|image|d.jpg', 'upload|image|c.jpg', 'upload|image|a.jpg'], self::remoteIds($reloaded));
        self::assertSame([$reloadedIds[1], $reloadedIds[0], $reloadedIds[2]], self::locationIds($reloaded));
    }

    public function testSubmitWithoutPayloadKeepsStoredGallery(): void
    {
        $galleryId = $this->storeGallery('upload|image|a.jpg', 'upload|image|b.jpg');

        $gallery = $this->loadGallery($galleryId);
        $locationIds = self::locationIds($gallery);

        $form = $this->createForm($gallery);
        $form->submit(['images' => []]);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));

        $this->entityManager->flush();

        $reloaded = $this->loadGallery($galleryId);

        self::assertSame(['upload|image|a.jpg', 'upload|image|b.jpg'], self::remoteIds($reloaded));
        self::assertSame($locationIds, self::locationIds($reloaded));
    }

    public function testSubmitEmptyListEmptiesStoredGallery(): void
    {
        $galleryId = $this->storeGallery('upload|image|a.jpg', 'upload|image|b.jpg');

        $form = $this->createForm($this->loadGallery($galleryId));
        $form->submit(['images' => ['collectionPayload' => '[]']]);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));

        $this->entityManager->flush();

        self::assertSame([], self::remoteIds($this->loadGallery($galleryId)));
        self::assertSame([], $this->entityManager->getRepository(RemoteResourceLocation::class)->findAll());
    }

    public function testLimitOneFieldReplacesItsOnlyLocation(): void
    {
        $galleryId = $this->storeGallery('upload|image|a.jpg');
        $this->storeResource('upload|image|b.jpg');

        $form = $this->createForm($this->loadGallery($galleryId), ['upload_limit' => 1]);
        $form->submit([
            'images' => [
                'collectionPayload' => json_encode([['remoteId' => 'upload|image|b.jpg']]),
            ],
        ]);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));

        $this->entityManager->flush();

        $reloaded = $this->loadGallery($galleryId);

        self::assertSame(['upload|image|b.jpg'], self::remoteIds($reloaded));
        self::assertSame([0], self::positions($reloaded));
        self::assertCount(1, $this->entityManager->getRepository(RemoteResourceLocation::class)->findAll());
    }

    public function testRejectedSubmissionLeavesStoredGalleryUntouched(): void
    {
        $galleryId = $this->storeGallery('upload|image|a.jpg', 'upload|image|b.jpg');

        $gallery = $this->loadGallery($galleryId);
        [$a, $b] = self::locationIds($gallery);

        $form = $this->createForm($gallery, ['upload_limit' => 1]);
        $form->submit([
            'images' => [
                'collectionPayload' => json_encode([
                    ['locationId' => $b, 'remoteId' => 'upload|image|b.jpg', 'watermarkText' => 'Rejected'],
                    ['locationId' => $a, 'remoteId' => 'upload|image|a.jpg'],
                ]),
            ],
        ]);

        self::assertFalse($form->isValid());

        // The rejected order is what gets rendered again.
        self::assertSame(
            ['upload|image|b.jpg', 'upload|image|a.jpg'],
            array_map(
                static fn (RemoteResourceLocation $location): string => $location->getRemoteResource()->getRemoteId(),
                $form->createView()->children['images']->vars['remote_media_locations'],
            ),
        );

        // Even if the application flushes for another reason, nothing of it gets stored.
        $this->entityManager->flush();

        $reloaded = $this->loadGallery($galleryId);

        self::assertSame(['upload|image|a.jpg', 'upload|image|b.jpg'], self::remoteIds($reloaded));
        self::assertNull($reloaded->getImages()[1]->getWatermarkText());
    }

    private static function createEntityManager(): EntityManagerInterface
    {
        $mappingDriver = new MappingDriverChain();
        $mappingDriver->addDriver(
            new SimplifiedXmlDriver([__DIR__ . '/../../../../bundle/Resources/config/doctrine' => 'Netgen\RemoteMedia\API\Values']),
            'Netgen\RemoteMedia\API\Values',
        );
        $mappingDriver->addDriver(
            new AttributeDriver([__DIR__ . '/Fixtures']),
            'Netgen\RemoteMedia\Tests\Form\Type\Fixtures',
        );

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl($mappingDriver);
        $configuration->setMetadataCache(new ArrayAdapter());
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('NetgenRemoteMediaTestProxies');
        $configuration->setAutoGenerateProxyClasses(true);

        $entityManager = EntityManager::create(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $configuration),
            $configuration,
        );

        // The collation of the remote ID column exists only on MySQL.
        unset($entityManager->getClassMetadata(RemoteResource::class)->fieldMappings['remoteId']['options']['collation']);

        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        return $entityManager;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createForm(Gallery $gallery, array $options = []): FormInterface
    {
        return $this->formFactory
            ->createBuilder(FormType::class, $gallery, ['data_class' => Gallery::class])
            ->add('images', RemoteMediaType::class, $options)
            ->getForm();
    }

    private function storeResource(string $remoteId): RemoteResource
    {
        $resource = new RemoteResource(
            remoteId: $remoteId,
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://example.test/' . $remoteId,
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            name: $remoteId,
            originalFilename: $remoteId,
        );

        $this->entityManager->persist($resource);
        $this->entityManager->flush();

        return $resource;
    }

    /**
     * Stores a gallery the way it was stored before locations had a position: in the order of their IDs.
     *
     * @return int ID of the gallery
     */
    private function storeGallery(string ...$remoteIds): int
    {
        $gallery = new Gallery();
        foreach ($remoteIds as $remoteId) {
            $gallery->addImage(new RemoteResourceLocation($this->storeResource($remoteId)));
        }

        $this->entityManager->persist($gallery);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $gallery->getId();
    }

    private function loadGallery(int $id): Gallery
    {
        $this->entityManager->clear();

        return $this->entityManager->find(Gallery::class, $id);
    }

    /**
     * @return array<int, string>
     */
    private static function remoteIds(Gallery $gallery): array
    {
        return array_map(
            static fn (RemoteResourceLocation $location): string => $location->getRemoteResource()->getRemoteId(),
            $gallery->getImages()->getValues(),
        );
    }

    /**
     * @return array<int, int>
     */
    private static function locationIds(Gallery $gallery): array
    {
        return array_map(
            static fn (RemoteResourceLocation $location): int => $location->getId(),
            $gallery->getImages()->getValues(),
        );
    }

    /**
     * @return array<int, int|null>
     */
    private static function positions(Gallery $gallery): array
    {
        return array_map(
            static fn (RemoteResourceLocation $location): ?int => $location->getPosition(),
            $gallery->getImages()->getValues(),
        );
    }
}
