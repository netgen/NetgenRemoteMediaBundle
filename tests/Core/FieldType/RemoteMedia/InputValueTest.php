<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use PHPUnit\Framework\TestCase;
use function json_encode;

class InputValueTest extends TestCase
{
    public function testInstanceOfValue()
    {
        self::assertInstanceOf(BaseValue::class, new InputValue());
    }

    public function testConstructionWithParameters()
    {
        $parameters = [
            'input_uri' => 'test/path/image.jpg',
            'alt_text' => 'Test alt text',
            'caption' => 'Test caption',
            'variations' => [],
        ];

        $inputValue = new InputValue($parameters);

        self::assertEquals(json_encode($parameters), (string) $inputValue);
    }

    public function testConstructionWithoutParameters()
    {
        $expectedResponseArray = [
            'input_uri' => null,
            'alt_text' => '',
            'caption' => '',
            'variations' => [],
        ];

        $inputValue = new InputValue();

        self::assertEquals(json_encode($expectedResponseArray), (string) $inputValue);
    }
}
