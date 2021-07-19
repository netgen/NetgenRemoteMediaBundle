<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    protected $value;

    protected function setUp()
    {
        $this->value = new Value(
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
