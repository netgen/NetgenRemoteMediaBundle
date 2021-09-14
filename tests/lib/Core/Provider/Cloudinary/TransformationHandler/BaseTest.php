<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected RemoteResource $resource;

    protected function setUp(): void
    {
        $this->resource = new RemoteResource(
            [
                'resourceId' => 'testId',
                'url' => 'http://cloudinary.com/some/url',
                'secure_url' => 'https://cloudinary.com/some/url',
                'variations' => [
                    'small' => [
                        'x' => 10,
                        'y' => 10,
                        'w' => 300,
                        'h' => 200,
                    ],
                ],
            ],
        );
    }
}
