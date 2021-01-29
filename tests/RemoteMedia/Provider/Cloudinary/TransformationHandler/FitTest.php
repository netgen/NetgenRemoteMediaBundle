<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Fit;

class FitTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Fit
     */
    protected $fit;

    public function setUp()
    {
        parent::setUp();
        $this->fit = new Fit();
    }

    public function testFitSimple()
    {
        $this->assertEquals(
            ['crop' => 'fit'],
            $this->fit->process($this->value, 'small')
        );
    }

    public function testFitWithDimensions()
    {
        $this->assertEquals(
            [
                'crop' => 'fit',
                'width' => 100,
                'height' => 200,
            ],
            $this->fit->process($this->value, 'small', [100, 200])
        );
    }
}
