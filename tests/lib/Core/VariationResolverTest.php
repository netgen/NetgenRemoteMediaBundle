<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core;

use Netgen\RemoteMedia\Core\VariationResolver;
use PHPUnit\Framework\TestCase;

final class VariationResolverTest extends TestCase
{
    protected VariationResolver $variationResolver;

    protected function setUp(): void
    {
        $this->variationResolver = new VariationResolver();
        $this->variationResolver->setVariations($this->getVariationSet());
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\VariationResolver::getVariationsForGroup
     */
    public function testGroupVariationsWithOverride(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
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
                'non_cropbbable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
            $this->variationResolver->getVariationsForGroup('article'),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\VariationResolver::getVariationsForGroup
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
                'non_cropbbable' => [
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
     * @covers \Netgen\RemoteMedia\Core\VariationResolver::getCroppbableVariations
     */
    public function testCroppableVariations(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
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
                'non_cropbbable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
            'article' => [
                'small' => [
                    'transformations' => [
                        'crop' => [200, 200],
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
