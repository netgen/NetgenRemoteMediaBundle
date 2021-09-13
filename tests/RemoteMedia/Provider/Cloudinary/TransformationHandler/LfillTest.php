<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill;

class LfillTest extends BaseTest
{
    /**
     * @var \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lfill
     */
    protected $lfill;

    protected function setUp()
    {
        parent::setUp();
        $this->lfill = new Lfill();
    }

    public function testLfillSimple()
    {
        self::assertEquals(
            ['crop' => 'lfill'],
            $this->lfill->process($this->value, 'small'),
        );
    }

    public function testLfillWithDimensions()
    {
        self::assertEquals(
            [
                'crop' => 'lfill',
                'width' => 100,
                'height' => 200,
            ],
            $this->lfill->process($this->value, 'small', [100, 200]),
        );
    }
}
