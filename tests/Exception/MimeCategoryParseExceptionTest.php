<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Exception;

use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use PHPUnit\Framework\TestCase;

class MimeCategoryParseExceptionTest extends TestCase
{
    /**
     * @expectedException \Netgen\RemoteMedia\Exception\MimeCategoryParseException
     * @expectedExceptionMessage Could not parse mime category for given mime type: mimetype.
     */
    public function testException()
    {
        throw new MimeCategoryParseException('mimetype');
    }
}
