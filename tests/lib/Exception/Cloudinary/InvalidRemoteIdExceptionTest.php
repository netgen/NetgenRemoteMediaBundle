<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception\Cloudinary;

use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;
use PHPUnit\Framework\TestCase;

final class InvalidRemoteIdExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException::__construct
     */
    public function testException(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('Invalid data has been provided to the remote resource factory.');

        throw new InvalidDataException();
    }
}
