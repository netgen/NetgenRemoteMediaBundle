<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use PHPUnit\Framework\TestCase;

class VariationResolverTest extends TestCase
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver */
    protected $variationResolver;

    public function setUp()
    {
        $this->variationResolver = new VariationResolver();
        $this->variationResolver->setVariations($this->getVariationSet());
    }

    public function testContentTypeVariationsWithOverride()
    {
        $this->assertEquals(
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
            $this->variationResolver->getVariationsForContentType('article')
        );
    }

    public function testContentTypeVariationsWithOutOverride()
    {
        $this->assertEquals(
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
            $this->variationResolver->getVariationsForContentType('blog_post')
        );
    }

    public function testCroppableVariations()
    {
        $this->assertEquals(
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
            $this->variationResolver->getCroppbableVariations('article')
        );
    }

    public function testGetEmbedVariations()
    {
        $this->assertEquals(
            [
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
            ],
            $this->variationResolver->getEmbedVariations()
        );
    }

    protected function getVariationSet()
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
