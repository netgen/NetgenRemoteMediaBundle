<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Transformation;

use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    /**
     * @var \Netgen\RemoteMedia\Core\Transformation\Registry
     */
    protected $registry;

    protected $cropTransformation;

    protected $resizeTransformation;

    protected $otherProviderCropTransformation;

    protected function setUp()
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
        self::assertEquals(
            $this->cropTransformation,
            $this->registry->getHandler('crop', 'cloudinary'),
        );
    }

    public function testNoHandler()
    {
        $this->expectException(TransformationHandlerNotFoundException::class);

        $this->registry->getHandler('cloudinary', 'something');
    }
}
