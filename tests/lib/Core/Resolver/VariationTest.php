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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class VariationTest extends TestCase
{
    protected VariationResolver $variationResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Transformation\HandlerInterface */
    private MockObject $cropHandler;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Transformation\HandlerInterface */
    private MockObject $formatHandler;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface */
    private MockObject $logger;

    protected function setUp(): void
    {
        $this->cropHandler = $this->createMock(HandlerInterface::class);
        $this->formatHandler = $this->createMock(HandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $registry = new Registry();
        $registry->addHandler('cloudinary', 'crop', $this->cropHandler);
        $registry->addHandler('cloudinary', 'format', $this->formatHandler);

        $this->variationResolver = new VariationResolver();
        $this->variationResolver->setServices($registry, $this->logger);
        $this->variationResolver->setVariations($this->getVariationSet());
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::getVariationsForGroup
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testGroupVariationsWithOverride(): void
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
            ],
            $this->variationResolver->getVariationsForGroup('article'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::getVariationsForGroup
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testContentTypeVariationsWithOutOverride(): void
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
            $this->variationResolver->getVariationsForGroup('blog_post'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::getCroppbableVariations
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testCroppableVariations(): void
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
            $this->variationResolver->getCroppbableVariations('article'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::processConfiguredVariation
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testProcessConfiguredVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 200),
        ];

        $location = new RemoteResourceLocation($resource, $cropSettings);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::processConfiguredVariation
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testProcessConfiguredVariationMissing(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::processConfiguredVariation
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testProcessConfiguredVariationMissingCropSettings(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $cropSettings = [
            new CropSettings('large', 5, 10, 800, 800),
        ];

        $location = new RemoteResourceLocation($resource, $cropSettings);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::processConfiguredVariation
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testProcessConfiguredVariationMissingTransformationHandler(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

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

    /**
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::processConfiguredVariation
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setServices
     * @covers \Netgen\RemoteMedia\Core\Resolver\Variation::setVariations
     */
    public function testProcessConfiguredVariationTransformationHandlerFailed(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $cropSettings = [
            new CropSettings('small', 5, 10, 200, 200),
        ];

        $location = new RemoteResourceLocation($resource, $cropSettings);

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
