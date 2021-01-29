<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lfill;

class LfillTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Lfill
     */
    protected $lfill;

    public function setUp()
    {
        parent::setUp();
        $this->lfill = new Lfill();
    }

    public function testLfillSimple()
    {
        $this->assertEquals(
            ['crop' => 'lfill'],
            $this->lfill->process($this->value, 'small')
        );
    }

    public function testLfillWithDimensions()
    {
        $this->assertEquals(
            [
                'crop' => 'lfill',
                'width' => 100,
                'height' => 200,
            ],
            $this->lfill->process($this->value, 'small', [100, 200])
        );
    }
}
