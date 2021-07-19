<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Limit;

class LimitTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Limit
     */
    protected $limit;

    protected function setUp()
    {
        parent::setUp();
        $this->limit = new Limit();
    }

    public function testLimitSimple()
    {
        self::assertEquals(
            ['crop' => 'limit'],
            $this->limit->process($this->value, 'small'),
        );
    }

    public function testLimitWithDimensions()
    {
        self::assertEquals(
            [
                'crop' => 'limit',
                'width' => 100,
                'height' => 200,
            ],
            $this->limit->process($this->value, 'small', [100, 200]),
        );
    }
}
