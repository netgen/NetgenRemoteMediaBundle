<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Exception;

use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use PHPUnit\Framework\TestCase;

class TransformationHandlerNotFoundExceptionTest extends TestCase
{
    /**
     * @expectedException \Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException
     * @expectedExceptionMessage [NgRemoteMedia] Transformation handler with 'some_handler' identifier for 'some_provider' provider not found.
     */
    public function testException()
    {
        throw new TransformationHandlerNotFoundException('some_provider', 'some_handler');
    }
}
