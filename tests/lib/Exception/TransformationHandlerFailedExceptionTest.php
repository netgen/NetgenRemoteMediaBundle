<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformationHandlerFailedException::class)]
final class TransformationHandlerFailedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);
        $this->expectExceptionMessage('Transformation handler "Test\Class" identifier failed');

        throw new TransformationHandlerFailedException('Test\Class');
    }
}
