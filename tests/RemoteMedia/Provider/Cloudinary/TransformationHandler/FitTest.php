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

    protected function setUp()
    {
        parent::setUp();
        $this->fit = new Fit();
    }

    public function testFitSimple()
    {
        self::assertEquals(
            ['crop' => 'fit'],
            $this->fit->process($this->value, 'small'),
        );
    }

    public function testFitWithDimensions()
    {
        self::assertEquals(
            [
                'crop' => 'fit',
                'width' => 100,
                'height' => 200,
            ],
            $this->fit->process($this->value, 'small', [100, 200]),
        );
    }
}
