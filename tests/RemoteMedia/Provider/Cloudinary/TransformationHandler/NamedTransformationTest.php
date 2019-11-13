<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\NamedTransformation;

class NamedTransformationTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\NamedTransformation
     */
    protected $namedTransformation;

    public function setUp()
    {
        parent::setUp();
        $this->namedTransformation = new NamedTransformation();
    }

    public function testNamedTransformation()
    {
        $this->assertEquals(
            ['transformation' => 'thisIsNamedTransformation'],
            $this->namedTransformation->process($this->value, 'named', ['thisIsNamedTransformation'])
        );
    }

    public function testMissingNamedTransformationConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->namedTransformation->process($this->value, 'named');
    }
}
