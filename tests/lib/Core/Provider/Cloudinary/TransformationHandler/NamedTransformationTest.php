<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\NamedTransformation;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamedTransformation::class)]
final class NamedTransformationTest extends TestCase
{
    protected NamedTransformation $namedTransformation;

    protected function setUp(): void
    {
        $this->namedTransformation = new NamedTransformation();
    }

    public function testNamedTransformation(): void
    {
        self::assertSame(
            ['transformation' => 'thisIsNamedTransformation'],
            $this->namedTransformation->process(['thisIsNamedTransformation']),
        );
    }

    public function testMissingNamedTransformationConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->namedTransformation->process();
    }
}
