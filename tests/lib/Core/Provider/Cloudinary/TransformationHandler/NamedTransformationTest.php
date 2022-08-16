<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class NamedTransformationTest extends TestCase
{
    protected NamedTransformation $namedTransformation;

    protected function setUp(): void
    {
        $this->namedTransformation = new NamedTransformation();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation::process
     */
    public function testNamedTransformation(): void
    {
        self::assertSame(
            ['transformation' => 'thisIsNamedTransformation'],
            $this->namedTransformation->process(['thisIsNamedTransformation']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation::process
     */
    public function testMissingNamedTransformationConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->namedTransformation->process();
    }
}
