<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Exception;

use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;
use PHPUnit\Framework\TestCase;

final class CropSettingsNotFoundExceptionTest extends TestCase
{
    /**
     * @covers \Netgen\RemoteMedia\Exception\CropSettingsNotFoundException::__construct
     */
    public function testException(): void
    {
        $this->expectException(CropSettingsNotFoundException::class);
        $this->expectExceptionMessage('Crop settings for variation "small" were not found.');

        throw new CropSettingsNotFoundException('small');
    }
}
