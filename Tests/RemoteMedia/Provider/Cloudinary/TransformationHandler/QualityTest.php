<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Quality;

class QualityTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Quality
     */
    protected $quality;

    public function setUp()
    {
        parent::setUp();
        $this->quality = new Quality();
    }

    public function testQualitySimple()
    {
        $this->assertEquals(
            array('quality' => 80),
            $this->quality->process($this->value, 'test', array(80))
        );
    }

    public function testQualityWithAutoType()
    {
        $this->assertEquals(
            array(
                'quality' => 'auto:best'
            ),
            $this->quality->process($this->value, 'test', array('auto', 'best'))
        );
    }

    public function testMissingNamedTransformationConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process($this->value, 'test');
    }
}
