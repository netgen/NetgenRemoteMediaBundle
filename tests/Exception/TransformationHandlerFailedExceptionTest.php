<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Exception;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

class TransformationHandlerFailedExceptionTest extends TestCase
{
    /**
     * @expectedException \Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException
     * @expectedExceptionMessage Transformation handler 'Test\Class' identifier failed
     */
    public function testException()
    {
        throw new TransformationHandlerFailedException('Test\\Class');
    }
}
