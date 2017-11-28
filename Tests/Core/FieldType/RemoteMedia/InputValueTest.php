<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use PHPUnit\Framework\TestCase;

class InputValueTest extends TestCase
{
    public function testInstanceOfValue()
    {
        $this->assertInstanceOf(BaseValue::class, new InputValue());
    }

    public function testConstructionWithParameters()
    {
        $parameters = array(
            'input_uri' => 'test/path/image.jpg',
            'alt_text' => 'Test alt text',
            'caption' => 'Test caption',
            'variations' => array(),
        );

        $inputValue = new InputValue($parameters);

        $this->assertEquals(json_encode($parameters), (string) $inputValue);
    }

    public function testConstructionWithoutParameters()
    {
        $expectedResponseArray = array(
            'input_uri' => null,
            'alt_text' => '',
            'caption' => '',
            'variations' => array(),
        );

        $inputValue = new InputValue();

        $this->assertEquals(json_encode($expectedResponseArray), (string) $inputValue);
    }
}
