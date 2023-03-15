<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception\Factory;

use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDataException::class)]
final class InvalidDataExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('Invalid data has been provided to the remote resource factory.');

        throw new InvalidDataException();
    }

    public function testExceptionWithCustomMessage(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('Factory expects "Cloudinary\Api\Response" as data, "array" given.');

        throw new InvalidDataException('Factory expects "Cloudinary\Api\Response" as data, "array" given.');
    }
}
