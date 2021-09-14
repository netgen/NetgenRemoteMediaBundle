<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class NamedTransformationTest extends BaseTest
{
    protected NamedTransformation $namedTransformation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namedTransformation = new NamedTransformation();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation::process
     */
    public function testNamedTransformation(): void
    {
        self::assertSame(
            ['transformation' => 'thisIsNamedTransformation'],
            $this->namedTransformation->process($this->resource, 'named', ['thisIsNamedTransformation']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation::process
     */
    public function testMissingNamedTransformationConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->namedTransformation->process($this->resource, 'named');
    }
}
