<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Exception;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use PHPUnit\Framework\TestCase;

class TransformationHandlerNotFoundxceptionTest extends TestCase
{
    /**
     * @expectedException \Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException
     * @expectedExceptionMessage [NgRemoteMedia] Transformation handler with 'some_handler' identifier for 'some_provider' provider not found.
     */
    public function testException()
    {
        throw new TransformationHandlerNotFoundException('some_provider', 'some_handler');
    }
}
