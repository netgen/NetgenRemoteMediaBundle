<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad;

class LpadTest extends BaseTest
{
    /**
     * @var \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Lpad
     */
    protected $lpad;

    protected function setUp()
    {
        parent::setUp();
        $this->lpad = new Lpad();
    }

    public function testLpadSimple()
    {
        self::assertEquals(
            ['crop' => 'lpad'],
            $this->lpad->process($this->value, 'small'),
        );
    }

    public function testLpadWithDimensions()
    {
        self::assertEquals(
            [
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200,
            ],
            $this->lpad->process($this->value, 'small', [100, 200]),
        );
    }

    public function testLpadWithDimensionsAndColour()
    {
        self::assertEquals(
            [
                'crop' => 'lpad',
                'width' => 100,
                'height' => 200,
                'background' => 'red',
            ],
            $this->lpad->process($this->value, 'small', [100, 200, 'red']),
        );
    }
}
