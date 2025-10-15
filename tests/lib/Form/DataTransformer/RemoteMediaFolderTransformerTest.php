<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\DataTransformer;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaFolderTransformer;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\DataTransformerInterface;

#[CoversClass(RemoteMediaFolderTransformer::class)]
class RemoteMediaFolderTransformerTest extends AbstractTestCase
{
    protected DataTransformerInterface $dataTransformer;

    protected function setUp(): void
    {
        $this->dataTransformer = new RemoteMediaFolderTransformer();
    }

    #[DataProvider('provideTransformCases')]
    public function testTransform(mixed $value, ?array $expectedData): void
    {
        self::assertSame(
            $expectedData,
            $this->dataTransformer->transform($value),
        );
    }

    public static function provideTransformCases(): iterable
    {
        return [
            [
                Folder::fromPath('media'),
                ['folder' => 'media'],
            ],
            [
                Folder::fromPath('media/images/upload/new/products'),
                ['folder' => 'media/images/upload/new/products'],
            ],
            [
                'test',
                null,
            ],
            [
                [],
                null,
            ],
            [
                ['folder' => 'media/images'],
                null,
            ],
            [
                null,
                null,
            ],
        ];
    }

    #[DataProvider('provideReverseTransformCases')]
    public function testReverseTransform(mixed $value, ?Folder $expectedData): void
    {
        if ($expectedData instanceof Folder) {
            AbstractTestCase::assertFolderSame(
                $expectedData,
                $this->dataTransformer->reverseTransform($value),
            );

            return;
        }

        self::assertNull($this->dataTransformer->reverseTransform($value));
    }

    public static function provideReverseTransformCases(): iterable
    {
        return [
            [
                ['folder' => 'media'],
                Folder::fromPath('media'),
            ],
            [
                ['folder' => 'media/images/upload/new/products'],
                Folder::fromPath('media/images/upload/new/products'),
            ],
            [
                'test',
                null,
            ],
            [
                [],
                null,
            ],
            [
                ['something' => 'media/images'],
                null,
            ],
            [
                null,
                null,
            ],
        ];
    }
}
