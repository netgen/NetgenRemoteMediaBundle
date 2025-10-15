<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Flags;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Flags::class)]
final class FlagsTest extends TestCase
{
    protected Flags $flags;

    protected function setUp(): void
    {
        $this->flags = new Flags();
    }

    #[DataProvider('provideCases')]
    public function test(array $input, array $output): void
    {
        self::assertSame(
            $output,
            $this->flags->process($input),
        );
    }

    public static function provideCases(): iterable
    {
        return [
            [['rasterize', 'test'], ['flags' => ['rasterize']]],
            [['any_format', 'rasterize', 'nonexisting'], ['flags' => ['any_format', 'rasterize']]],
            [['rasterize', 'attachment', 'force_strip', 'relative'], ['flags' => ['rasterize', 'attachment', 'force_strip', 'relative']]],
        ];
    }
}
