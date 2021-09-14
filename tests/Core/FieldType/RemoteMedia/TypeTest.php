<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Date\Value as DateValue;
use eZ\Publish\SPI\FieldType\FieldType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Type;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public const PARAMETERS = ['resourceId' => 'test'];

    public const INPUT_PARAMETERS = [
        'input_uri' => 'test/path/image.jpg',
        'alt_text' => 'Test alt text',
        'caption' => 'Test caption',
        'variations' => [],
    ];

    /**
     * @var Type
     */
    protected $type;

    protected $value;

    protected $emptyValue;

    protected $inputValue;

    protected $emptyInputValue;

    protected function setUp()
    {
        $this->type = new Type();
        $this->value = new Value(self::PARAMETERS);
        $this->emptyValue = new Value();
        $this->inputValue = new InputValue(self::INPUT_PARAMETERS);
        $this->emptyInputValue = new InputValue();
    }

    public function testInstanceOfFieldType()
    {
        self::assertInstanceOf(FieldType::class, $this->type);
    }

    public function testGetFieldTypeIdentifier()
    {
        self::assertSame('ngremotemedia', $this->type->getFieldTypeIdentifier());
    }

    public function testGetNameWithValue()
    {
        self::assertSame($this->value->resourceId, $this->type->getName($this->value));
    }

    public function testGetNameWithEmptyValue()
    {
        self::assertSame($this->emptyValue->resourceId, $this->type->getName($this->emptyValue));
    }

    public function testGetEmptyValue()
    {
        self::assertSame($this->emptyValue, $this->type->getEmptyValue());
    }

    public function testFromHash()
    {
        self::assertSame($this->inputValue, $this->type->fromHash(self::INPUT_PARAMETERS));
    }

    public function testFromHashWithEmptyHash()
    {
        self::assertSame($this->emptyValue, $this->type->fromHash(''));
    }

    public function testToHash()
    {
        self::assertSame((array) $this->value, $this->type->toHash($this->value));
    }

    public function testInputValueToPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => null,
                'externalData' => (array) $this->inputValue,
                'sortKey' => false,
            ],
        );

        self::assertSame($fieldValue, $this->type->toPersistenceValue($this->inputValue));
    }

    public function testValueToPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->value,
                'externalData' => $this->value,
                'sortKey' => false,
            ],
        );

        self::assertSame($fieldValue, $this->type->toPersistenceValue($this->value));
    }

    public function testInvalidValueToPersistenceValue()
    {
        $spiValue = new DateValue();

        self::assertNull($this->type->toPersistenceValue($spiValue));
    }

    public function testValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->value,
            ],
        );

        self::assertSame($this->value, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testParametersValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => self::PARAMETERS,
            ],
        );

        self::assertSame($this->value, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testNullValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => null,
            ],
        );

        self::assertSame($this->emptyValue, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testEmptyValueFromPersistenceValue()
    {
        $fieldValue = new FieldValue(
            [
                'data' => $this->emptyValue,
            ],
        );

        self::assertSame($this->emptyValue, $this->type->fromPersistenceValue($fieldValue));
    }

    public function testIsEmptyValueWithInputValue()
    {
        self::assertNotTrue($this->type->isEmptyValue($this->inputValue));
    }

    public function testIsEmptyValueWithValue()
    {
        self::assertNotTrue($this->type->isEmptyValue($this->value));
    }

    public function testIsEmptyValueWithEmptyInputValue()
    {
        self::assertTrue($this->type->isEmptyValue($this->emptyInputValue));
    }

    public function testIsEmptyValueWithEmptyValue()
    {
        self::assertTrue($this->type->isEmptyValue($this->emptyValue));
    }

    public function testIsEmptyValueWithValueWithoutResourceId()
    {
        $spiValue = new Value(['url' => 'test']);

        self::assertTrue($this->type->isEmptyValue($spiValue));
    }

    public function testIsSearchableShouldAlwaysReturnTrue()
    {
        self::assertTrue($this->type->isSearchable());
    }

    public function testAcceptValueWithSingle()
    {
        $value = new InputValue(['input_uri' => 'test_uri']);

        $returnedValue = $this->type->acceptValue('test_uri');

        self::assertSame($value, $returnedValue);
    }

    public function testAcceptValueWithValidArray()
    {
        $returnedValue = $this->type->acceptValue(self::INPUT_PARAMETERS);

        self::assertSame($this->inputValue, $returnedValue);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testAcceptValueWithInvalidArray()
    {
        $returnedValue = $this->type->acceptValue([1]);

        self::assertSame(1, $returnedValue);
    }

    public function testAcceptValueWithInputValueObject()
    {
        self::assertSame($this->inputValue, $this->type->acceptValue($this->inputValue));
    }

    public function testAcceptValueWithValueObject()
    {
        self::assertSame($this->value, $this->type->acceptValue($this->value));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueWithInvalidStructuredInputValueObject()
    {
        $value = new InputValue(['input_uri' => ['test_uri']]);

        self::assertSame($value, $this->type->acceptValue($value));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueWithInvalidStructuredValueObject()
    {
        $value = new Value(['resourceId' => ['test']]);

        self::assertSame($value, $this->type->acceptValue($value));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testAcceptValueWithInvalidValueObjectType()
    {
        $value = 1;

        self::assertSame($value, $this->type->acceptValue($value));
    }
}
