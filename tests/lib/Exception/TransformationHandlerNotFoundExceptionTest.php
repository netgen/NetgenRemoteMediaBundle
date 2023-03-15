<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformationHandlerNotFoundException::class)]
final class TransformationHandlerNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(TransformationHandlerNotFoundException::class);
        $this->expectExceptionMessage('Transformation handler with "some_handler" identifier for "some_provider" provider not found.');

        throw new TransformationHandlerNotFoundException('some_provider', 'some_handler');
    }
}
