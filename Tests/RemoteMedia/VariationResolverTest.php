<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use PHPUnit\Framework\TestCase;

class VariationResolverTest extends TestCase
{
    /** @var  \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver */
    protected $variationResolver;

    protected function getVariationSet()
    {
        return array(
            'default' => array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(100, 100)
                    )
                ),
                'non_cropbbable' => array(
                    'transformations' => array(
                        'resize' => array(100, 100)
                    )
                )
            ),
            'article' => array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(200, 200)
                    )
                ),
                'large' => array(
                    'transformations' => array(
                        'crop'  => array(400, 400)
                    )
                ),
                'non_croppable_article' => array(
                    'transformations' => array(
                        'resize'  => array(100, 100)
                    )
                )
            ),
            'blog_post' => array(
                'header' => array(
                    'transformations' => array(
                        'crop' => array(1600, 900)
                    )
                ),
            ),
            'embedded' => array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(400, 250)
                    )
                ),
                'medium' => array(
                    'transformations' => array(
                        'crop' => array(800, 450)
                    )
                ),
                'large' => array(
                    'transformations' => array(
                        'crop' => array(1600, 900)
                    )
                ),
                'not_valid_embedded' => array(
                    'transformations' => array(
                        'resize' => array(200, 200)
                    )
                )
            )
        );
    }

    public function setUp()
    {
        $this->variationResolver = new VariationResolver();
        $this->variationResolver->setVariations($this->getVariationSet());
    }

    public function testContentTypeVariationsWithOverride()
    {

        $this->assertEquals(
            array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(200, 200)
                    )
                ),
                'large' => array(
                    'transformations' => array(
                        'crop'  => array(400, 400)
                    )
                ),
                'non_croppable_article' => array(
                    'transformations' => array(
                        'resize'  => array(100, 100)
                    )
                ),
                'non_cropbbable' => array(
                    'transformations' => array(
                        'resize' => array(100, 100)
                    )
                )
            ),
            $this->variationResolver->getVariationsForContentType('article')
        );
    }

    public function testContentTypeVariationsWithOutOverride()
    {

        $this->assertEquals(
            array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(100, 100)
                    )
                ),
                'non_cropbbable' => array(
                    'transformations' => array(
                        'resize' => array(100, 100)
                    )
                ),
                'header' => array(
                    'transformations' => array(
                        'crop' => array(1600, 900)
                    )
                ),
            ),
            $this->variationResolver->getVariationsForContentType('blog_post')
        );
    }

    public function testCroppableVariations()
    {
        $this->assertEquals(
            array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(200, 200)
                    )
                ),
                'large' => array(
                    'transformations' => array(
                        'crop'  => array(400, 400)
                    )
                ),
            ),
            $this->variationResolver->getCroppbableVariations('article')
        );
    }

    public function testGetEmbedVariations()
    {
        $this->assertEquals(
            array(
                'small' => array(
                    'transformations' => array(
                        'crop' => array(400, 250)
                    )
                ),
                'medium' => array(
                    'transformations' => array(
                        'crop' => array(800, 450)
                    )
                ),
                'large' => array(
                    'transformations' => array(
                        'crop' => array(1600, 900)
                    )
                ),
            ),
            $this->variationResolver->getEmbedVariations()
        );
    }
}
