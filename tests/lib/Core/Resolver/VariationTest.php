<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Resolver;

use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(VariationResolver::class)]
final class VariationTest extends TestCase
{
    protected VariationResolver $variationResolver;

    private MockObject|HandlerInterface $cropHandler;

    private MockObject|HandlerInterface $formatHandler;

    private MockObject|HandlerInterface $watermarkTextHandler;

    private MockObject|LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->cropHandler = $this->createMock(HandlerInterface::class);
        $this->formatHandler = $this->createMock(HandlerInterface::class);
        $this->watermarkTextHandler = $this->createMock(HandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $registry = new Registry();
        $registry->addHandler('cloudinary', 'crop', $this->cropHandler);
        $registry->addHandler('cloudinary', 'format', $this->formatHandler);
        $registry->addHandler('cloudinary', 'watermark_text', $this->watermarkTextHandler);

        $this->variationResolver = new VariationResolver(
            $registry,
            $this->logger,
            $this->getVariationSet(),
        );
    }

    public function testGetAvailableVariations(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
            $this->variationResolver->getAvailableVariations(),
        );
    }

    public function testGetAvailableVariationsForGroup(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
                        'format' => ['jpeg'],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
                'large' => [
                    'transformations' => [
                        'crop' => [400, 400],
                    ],
                ],
                'non_croppable_article' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
                'watermarked' => [
                    'transformations' => [
                        'watermark_text' => [
                            'text' => 'Fallback watermark',
                            'font_family' => 'Helvetica',
                            'font_size' => 18,
                            'color' => 'red',
                            'density' => 400,
                            'opacity' => 50,
                            'align' => 'top',
                            'y' => 20,
                        ],
                    ],
                ],
            ],
            $this->variationResolver->getAvailableVariations('article'),
        );
    }

    public function testGetAvailableVariationsWithoutOverride(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
                'header' => [
                    'transformations' => [
                        'crop' => [1600, 900],
                    ],
                ],
            ],
            $this->variationResolver->getAvailableVariations('blog_post'),
        );
    }

    public function testGetAvailableCroppableVariations(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
            ],
            $this->variationResolver->getAvailableCroppableVariations(),
        );
    }

    public function testGetAvailableCroppableVariationsForGroup(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
                        'format' => ['jpeg'],
                    ],
                ],
                'large' => [
                    'transformations' => [
                        'crop' => [400, 400],
                    ],
                ],
            ],
            $this->variationResolver->getAvailableCroppableVariations('article'),
        );
    }

    public function testProcessConfiguredVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 200),
        ];

        $location = new RemoteResourceLocation(
            remoteResource: $resource,
            cropSettings: $cropSettings,
        );

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 200,
            'crop' => 'crop',
        ];

        $formatOptions = ['fetch_format' => 'jpeg'];

        $transformations = [$cropOptions, $formatOptions];

        $this->cropHandler
            ->expects(self::once())
            ->method('process')
            ->with([5, 10, 200, 200])
            ->willReturn($cropOptions);

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['jpeg'])
            ->willReturn($formatOptions);

        self::assertSame(
            $transformations,
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'small',
            ),
        );
    }

    public function testProcessConfiguredVariationMissing(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        self::assertEmpty(
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'blogpost',
                'huge_banner',
            ),
        );
    }

    public function testProcessConfiguredVariationMissingCropSettings(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $cropSettings = [
            new CropSettings('large', 5, 10, 800, 800),
        ];

        $location = new RemoteResourceLocation(
            remoteResource: $resource,
            cropSettings: $cropSettings,
        );

        $formatOptions = ['fetch_format' => 'jpeg'];

        $transformations = [$formatOptions];

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['jpeg'])
            ->willReturn($formatOptions);

        self::assertSame(
            $transformations,
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'small',
            ),
        );
    }

    public function testProcessConfiguredVariationMissingTransformationHandler(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        $this->logger
            ->expects(self::once())
            ->method('notice')
            ->with('[NGRM] Transformation handler with "resize" identifier for "cloudinary" provider not found.');

        self::assertEmpty(
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'non_croppable',
            ),
        );
    }

    public function testProcessConfiguredVariationTransformationHandlerFailed(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 200),
        ];

        $location = new RemoteResourceLocation(
            remoteResource: $resource,
            cropSettings: $cropSettings,
        );

        $formatOptions = ['fetch_format' => 'jpeg'];

        $transformations = [$formatOptions];

        $this->formatHandler
            ->expects(self::once())
            ->method('process')
            ->with(['jpeg'])
            ->willReturn($formatOptions);

        $this->cropHandler
            ->expects(self::once())
            ->method('process')
            ->with([5, 10, 200, 200])
            ->willThrowException(new TransformationHandlerFailedException(Crop::class));

        self::assertSame(
            $transformations,
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'small',
            ),
        );
    }

    public function testProcessConfiguredVariationWithWatermarkText(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation(
            remoteResource: $resource,
            watermarkText: 'Test text',
        );

        $transformations = [
            'overlay' => [
                'text' => 'Some sample text',
                'font_family' => 'Helvetica',
                'font_size' => 18,
            ],
            'gravity' => 'north',
            'color' => 'red',
            'density' => 400,
            'opacity' => 50,
            'align' => 'top',
            'y' => 20,
        ];

        $this->watermarkTextHandler
            ->expects(self::once())
            ->method('process')
            ->with([
                'font_family' => 'Helvetica',
                'font_size' => 18,
                'color' => 'red',
                'density' => 400,
                'opacity' => 50,
                'align' => 'top',
                'y' => 20,
                'text' => 'Test text',
            ])
            ->willReturn($transformations);

        self::assertSame(
            [$transformations],
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'watermarked',
            ),
        );
    }

    public function testProcessConfiguredVariationWithFallbackWatermarkText(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

        $location = new RemoteResourceLocation($resource);

        $transformations = [
            'overlay' => [
                'text' => 'Some sample text',
                'font_family' => 'Helvetica',
                'font_size' => 18,
            ],
            'gravity' => 'north',
            'color' => 'red',
            'density' => 400,
            'opacity' => 50,
            'align' => 'top',
            'y' => 20,
        ];

        $this->watermarkTextHandler
            ->expects(self::once())
            ->method('process')
            ->with([
                'font_family' => 'Helvetica',
                'font_size' => 18,
                'color' => 'red',
                'density' => 400,
                'opacity' => 50,
                'align' => 'top',
                'y' => 20,
                'text' => 'Fallback watermark',
            ])
            ->willReturn($transformations);

        self::assertSame(
            [$transformations],
            $this->variationResolver->processConfiguredVariation(
                $location,
                'cloudinary',
                'article',
                'watermarked',
            ),
        );
    }

    /**
     * @return array<string, array>
     */
    protected function getVariationSet(): array
    {
        return [
            'default' => [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
            'article' => [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
                        'format' => ['jpeg'],
                    ],
                ],
                'large' => [
                    'transformations' => [
                        'crop' => [400, 400],
                    ],
                ],
                'non_croppable_article' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
                'watermarked' => [
                    'transformations' => [
                        'watermark_text' => [
                            'text' => 'Fallback watermark',
                            'font_family' => 'Helvetica',
                            'font_size' => 18,
                            'color' => 'red',
                            'density' => 400,
                            'opacity' => 50,
                            'align' => 'top',
                            'y' => 20,
                        ],
                    ],
                ],
            ],
            'blog_post' => [
                'header' => [
                    'transformations' => [
                        'crop' => [1600, 900],
                    ],
                ],
            ],
            'embedded' => [
                'small' => [
                    'transformations' => [
                        'crop' => [400, 250],
                    ],
                ],
                'medium' => [
                    'transformations' => [
                        'crop' => [800, 450],
                    ],
                ],
                'large' => [
                    'transformations' => [
                        'crop' => [1600, 900],
                    ],
                ],
                'not_valid_embedded' => [
                    'transformations' => [
                        'resize' => [200, 200],
                    ],
                ],
            ],
        ];
    }
}
