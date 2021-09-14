<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use PHPUnit\Framework\TestCase;

final class MimeCategoryParseExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\MimeCategoryParseException::__construct
     */
    public function testException(): void
    {
        $this->expectException(MimeCategoryParseException::class);
        $this->expectExceptionMessage('Could not parse mime category for given mime type: mimetype.');

        throw new MimeCategoryParseException('mimetype');
    }
}
