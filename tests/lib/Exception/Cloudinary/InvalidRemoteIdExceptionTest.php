<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception\Cloudinary;

use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use PHPUnit\Framework\TestCase;

final class InvalidRemoteIdExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException::__construct
     */
    public function testException(): void
    {
        $this->expectException(InvalidRemoteIdException::class);
        $this->expectExceptionMessage('Provided remoteId "test" is invalid.');

        throw new InvalidRemoteIdException('test');
    }
}
