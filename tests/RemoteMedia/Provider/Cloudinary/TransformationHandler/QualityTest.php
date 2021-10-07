<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Quality;

class QualityTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Quality
     */
    protected $quality;

    protected function setUp()
    {
        parent::setUp();
        $this->quality = new Quality();
    }

    public function testQualitySimple()
    {
        self::assertEquals(
            ['quality' => 80],
            $this->quality->process($this->value, 'test', [80])
        );
    }

    public function testQualityWithAutoType()
    {
        self::assertEquals(
            [
                'quality' => 'auto:best',
            ],
            $this->quality->process($this->value, 'test', ['auto', 'best'])
        );
    }

    public function testQualityWithNonAutoType()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process($this->value, 'test', ['test', 'best']);
    }

    public function testMissingNamedTransformationConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process($this->value, 'test');
    }
}
