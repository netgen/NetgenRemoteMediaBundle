<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Form;

use Doctrine\Common\Collections\ArrayCollection;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension\RemoteMediaExtension;
use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\Bundle\RemoteMediaBundle\Translation\VueTranslations;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaTransformer;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\Validation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFunction;

use function array_column;
use function dirname;
use function json_decode;
use function json_encode;
use function strpos;

/**
 * Renders the widget of RemoteMediaType with the form theme and the interface template of the bundle.
 */
#[CoversNothing]
final class RemoteMediaWidgetTest extends TestCase
{
    private MockObject&ProviderInterface $providerMock;

    private FormFactoryInterface $formFactory;

    private Environment $twig;

    /**
     * @var array<int, RemoteResourceLocation>
     */
    private array $locations = [];

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->providerMock
            ->method('getSupportedVisibilities')
            ->willReturn(RemoteResource::SUPPORTED_VISIBILITIES);

        $this->providerMock
            ->method('getSupportedTypes')
            ->willReturn(RemoteResource::SUPPORTED_TYPES);

        $this->providerMock
            ->method('loadByRemoteId')
            ->willReturnCallback(function (string $remoteId): RemoteResource {
                foreach ($this->locations as $location) {
                    if ($location->getRemoteResource()->getRemoteId() === $remoteId) {
                        return $location->getRemoteResource();
                    }
                }

                self::fail('Unexpected remote ID ' . $remoteId);
            });

        $this->providerMock
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (RemoteResourceLocation $location, string $group, string $name): RemoteResourceVariation => new RemoteResourceVariation(
                    $location->getRemoteResource(),
                    $location->getRemoteResource()->getUrl() . '?variation=' . $name,
                ),
            );

        $service = new RemoteResourceService($this->providerMock);

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new RemoteMediaType(new RemoteMediaTransformer($this->providerMock, $service), $this->providerMock, $service))
            ->addExtension(new ValidatorExtension(Validation::createValidator(), false))
            ->getFormFactory();

        $this->twig = $this->createTwig();

        foreach ([6 => 1, 7 => 0] as $id => $position) {
            $this->locations[$id] = new RemoteResourceLocation(
                new RemoteResource(
                    remoteId: 'upload|image|' . $id . '.jpg',
                    type: RemoteResource::TYPE_IMAGE,
                    url: 'https://example.test/' . $id . '.jpg',
                    md5: 'md5',
                    name: $id . '.jpg',
                ),
                id: $id,
                position: $position,
            );
        }

        $this->locations[7]
            ->setAltTextOverride('Local "alt"')
            ->setCropSettings([new CropSettings('hero', 1, 2, 300, 200)]);
    }

    public function testWidgetRendersPayloadOnServerBeforeTheInterface(): void
    {
        $form = $this->formFactory->createNamed('gallery', RemoteMediaType::class, new ArrayCollection($this->locations), [
            'upload_limit' => 5,
            'location_source' => 'product_gallery',
        ]);

        $html = $this->renderWidget($form);
        $payloadInput = self::payloadInput($html);
        $container = self::container($html);

        self::assertSame('hidden', $payloadInput->getAttribute('type'));

        $entries = json_decode($payloadInput->getAttribute('value'), true);
        self::assertSame([7, 6], array_column($entries, 'locationId'));
        self::assertSame(['upload|image|7.jpg', 'upload|image|6.jpg'], array_column($entries, 'remoteId'));
        self::assertSame('Local "alt"', $entries[0]['altTextOverride']);
        self::assertSame('{"hero":{"x":1,"y":2,"w":300,"h":200}}', $entries[0]['cropSettings']);

        // The interface prints its own input of the same name inside the container. Being later in the
        // document it is the one PHP reads, so the one rendered on the server has to come first.
        self::assertLessThan(strpos($html, 'class="ngremotemedia-container"'), strpos($html, 'name="gallery[collectionPayload]"'));

        $config = json_decode($container->getAttribute('v-init:config'), true);
        self::assertSame(
            ['_collection' => 'gallery', 'collectionPayload' => 'gallery[collectionPayload]'],
            $config['inputFields'],
        );
        self::assertSame(5, $config['uploadLimit']);
        self::assertSame('product_gallery', $config['locationSource']);

        $selectedImage = json_decode($container->getAttribute('v-init:selected-image'), true);
        self::assertSame([7, 6], array_column($selectedImage['uploadedResources'], 'locationId'));
        self::assertSame('Local "alt"', $selectedImage['uploadedResources'][0]['altTextOverride']);
        self::assertSame(['hero' => ['x' => 1, 'y' => 2, 'w' => 300, 'h' => 200]], $selectedImage['uploadedResources'][0]['variations']);
        self::assertSame('https://example.test/7.jpg?variation=preview_image', $selectedImage['uploadedResources'][0]['previewUrl']);
    }

    public function testWidgetOfSingleResourceFieldSubmitsPayloadToo(): void
    {
        $form = $this->formFactory->createNamed('cover', RemoteMediaType::class, new ArrayCollection([$this->locations[6]]), [
            'upload_limit' => 1,
        ]);

        $html = $this->renderWidget($form);

        self::assertSame([6], array_column(json_decode(self::payloadInput($html, 'cover')->getAttribute('value'), true), 'locationId'));

        $config = json_decode(self::container($html)->getAttribute('v-init:config'), true);
        self::assertSame(['_collection' => 'cover', 'collectionPayload' => 'cover[collectionPayload]'], $config['inputFields']);
        self::assertSame(1, $config['uploadLimit']);
        self::assertSame('form_cover', $config['locationSource']);
    }

    public function testWidgetOfUnlimitedEmptyField(): void
    {
        $html = $this->renderWidget($this->formFactory->createNamed('gallery', RemoteMediaType::class));

        self::assertSame('[]', self::payloadInput($html)->getAttribute('value'));

        $container = self::container($html);
        self::assertSame(0, json_decode($container->getAttribute('v-init:config'), true)['uploadLimit']);
        self::assertSame([], json_decode($container->getAttribute('v-init:selected-image'), true)['uploadedResources']);
    }

    public function testWidgetAfterRejectedSubmissionRendersWhatWasSubmitted(): void
    {
        $form = $this->formFactory->createNamed('gallery', RemoteMediaType::class, new ArrayCollection($this->locations), [
            'upload_limit' => 1,
        ]);

        $form->submit([
            'collectionPayload' => json_encode([
                ['locationId' => 6, 'remoteId' => 'upload|image|6.jpg', 'watermarkText' => 'Submitted watermark'],
                ['locationId' => 7, 'remoteId' => 'upload|image|7.jpg', 'cropSettings' => '{"hero":{"x":9,"y":8,"w":70,"h":60}}'],
            ]),
        ]);

        self::assertFalse($form->isValid());

        $html = $this->renderWidget($form);

        $entries = json_decode(self::payloadInput($html)->getAttribute('value'), true);
        self::assertSame([6, 7], array_column($entries, 'locationId'));
        self::assertSame('Submitted watermark', $entries[0]['watermarkText']);

        $resources = json_decode(self::container($html)->getAttribute('v-init:selected-image'), true)['uploadedResources'];
        self::assertSame([6, 7], array_column($resources, 'locationId'));
        self::assertSame('Submitted watermark', $resources[0]['watermarkText']);
        self::assertSame(['hero' => ['x' => 9, 'y' => 8, 'w' => 70, 'h' => 60]], $resources[1]['variations']);
    }

    private function createTwig(): Environment
    {
        $loader = new FilesystemLoader();
        $loader->addPath(dirname((new ReflectionClass(FormExtension::class))->getFileName(), 2) . '/Resources/views/Form');
        $loader->addPath(__DIR__ . '/../../../bundle/Resources/views', 'NetgenRemoteMedia');

        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new FormExtension());
        $twig->addExtension(new TranslationExtension());
        $twig->addExtension(new RemoteMediaExtension());
        $twig->addFunction(new TwigFunction('path', static fn (string $name): string => '/' . $name));

        $rendererEngine = new TwigRendererEngine(
            ['form_div_layout.html.twig', '@NetgenRemoteMedia/form/remote_media_type.html.twig'],
            $twig,
        );

        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => static fn (): FormRenderer => new FormRenderer($rendererEngine),
            RemoteMediaRuntime::class => fn (): RemoteMediaRuntime => new RemoteMediaRuntime(
                $this->providerMock,
                new VariationResolver(new Registry(), null),
            ),
            VueTranslations::class => static fn (): VueTranslations => new VueTranslations(new IdentityTranslator()),
        ]));

        return $twig;
    }

    private function renderWidget(FormInterface $form): string
    {
        return $this->twig->createTemplate('{{ form_widget(form) }}')->render(['form' => $form->createView()]);
    }

    private static function payloadInput(string $html, string $formName = 'gallery'): DOMElement
    {
        return self::query($html, '//input[@name="' . $formName . '[collectionPayload]"]');
    }

    private static function container(string $html): DOMElement
    {
        return self::query($html, '//div[@class="ngremotemedia-container"]');
    }

    private static function query(string $html, string $expression): DOMElement
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>');

        $nodes = (new DOMXPath($document))->query($expression);
        self::assertSame(1, $nodes->length, $expression);

        $node = $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node);

        return $node;
    }
}
