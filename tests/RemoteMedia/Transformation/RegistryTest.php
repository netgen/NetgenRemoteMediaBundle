<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Transformation;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Fit;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry
     */
    protected $registry;

    protected $cropTransformation;

    protected $resizeTransformation;

    protected $otherProviderCropTransformation;

    public function setUp()
    {
        $this->registry = new Registry();

        $this->cropTransformation = new Crop();
        $this->resizeTransformation = new Resize();
        $this->otherProviderCropTransformation = new Fit();

        $this->registry->addHandler('cloudinary', 'crop', $this->cropTransformation);
        $this->registry->addHandler('cloudinary', 'resize', $this->resizeTransformation);
        $this->registry->addHandler('otherprovider', 'crop', $this->otherProviderCropTransformation);
    }

    public function testGetHandler()
    {
        $this->assertEquals(
            $this->cropTransformation,
            $this->registry->getHandler('crop', 'cloudinary')
        );
    }

    public function testNoHandler()
    {
        $this->expectException(TransformationHandlerNotFoundException::class);

        $this->registry->getHandler('cloudinary', 'something');
    }
}
