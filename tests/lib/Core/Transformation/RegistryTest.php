<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Transformation;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Crop;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Fit;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Resize;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Registry::class)]
class RegistryTest extends TestCase
{
    protected Registry $registry;

    protected HandlerInterface $cropTransformation;

    protected HandlerInterface $resizeTransformation;

    protected HandlerInterface $otherProviderCropTransformation;

    protected function setUp(): void
    {
        $this->registry = new Registry();

        $this->cropTransformation = new Crop();
        $this->resizeTransformation = new Resize();
        $this->otherProviderCropTransformation = new Fit();

        $this->registry->addHandler('cloudinary', 'crop', $this->cropTransformation);
        $this->registry->addHandler('cloudinary', 'resize', $this->resizeTransformation);
        $this->registry->addHandler('otherprovider', 'crop', $this->otherProviderCropTransformation);
    }

    public function testGetHandler(): void
    {
        self::assertSame(
            $this->cropTransformation,
            $this->registry->getHandler('crop', 'cloudinary'),
        );
    }

    public function testNoHandler(): void
    {
        $this->expectException(TransformationHandlerNotFoundException::class);

        $this->registry->getHandler('cloudinary', 'something');
    }
}
