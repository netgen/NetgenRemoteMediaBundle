<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception\Cloudinary;

use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidRemoteIdException::class)]
final class InvalidRemoteIdExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(InvalidRemoteIdException::class);
        $this->expectExceptionMessage('Provided remoteId "test" is invalid.');

        throw new InvalidRemoteIdException('test');
    }
}
