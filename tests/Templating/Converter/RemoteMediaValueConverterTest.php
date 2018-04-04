<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Converter;

use Netgen\Bundle\RemoteMediaBundle\Templating\Converter\RemoteMediaValueConverter;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use PHPUnit\Framework\TestCase;

class RemoteMediaValueConverterTest extends TestCase
{
    protected $converter;

    public function setUp()
    {
        $this->converter = new RemoteMediaValueConverter();
    }

   public function testConvert()
   {
       $object = new Value();

       $this->assertEquals($object, $this->converter->convert($object));
   }
}
