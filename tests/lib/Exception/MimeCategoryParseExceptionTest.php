<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MimeCategoryParseException::class)]
final class MimeCategoryParseExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(MimeCategoryParseException::class);
        $this->expectExceptionMessage('Could not parse mime category for given mime type: mimetype.');

        throw new MimeCategoryParseException('mimetype');
    }
}
