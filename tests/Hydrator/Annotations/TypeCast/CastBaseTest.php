<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Class CastBaseTest.
 */
class CastBaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the basics of the class.
     *
     * @test
     */
    public function testCheckBasics()
    {
        $class = new \ReflectionClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $this->assertTrue($class->isAbstract());
        $this->assertTrue($class->hasProperty('allowNull'));
        $defaults = $class->getDefaultProperties();
        $this->assertFalse($defaults['allowNull']);
        $expectedConstants = [
            'ONLY_STRINGS_NUMERICS' => 'Only strings or numerics are accepted',

            'ONLY_BOOLS_STRINGS_NUMERICS' => 'Only booleans, strings or numerics are accepted',

            'BOOL_MUST_BE_ACCEPTED_FORMAT' => 'Value must either be the boolean true (or %trues%) or false (or %falses%)',

            'DATETIME_MUST_BE_ACCEPTED_FORMAT' => 'Unable to interpret as a valid date time in expected range - '.
                                                  'expected either an '.
                                                  'ISO8601/RFC3339 or RFC-2822 formatted date or unix datestamp',

            'DATETIME_OUTSIDE_ACCEPTABLE_RANGE' => 'Date must be between %min% and %max%',

            'FLOAT_MUST_BE_ACCEPTED_FORMAT'    => 'Value must be a float using the decimal separator '.
                                                  '%decimalSeparator% and the optional digits separator %digitsSeparator%',
            'DECIMAL_MUST_BE_ACCEPTED_FORMAT'  => 'Value must be a decimal using the optional digits '.
                                                  'separator %digitsSeparator%',
            'DECIMAL_OUTSIDE_ACCEPTABLE_RANGE' => 'Decimal must be between %min% and %max%'
        ];
        $constants         = $class->getConstants();
        $this->assertCount(
            count($expectedConstants),
            $constants,
            'Found '.count($constants).' when expecting '.count($expectedConstants)
        );
        foreach ($expectedConstants as $k => $v) {
            $this->assertArrayHasKey($k, $constants);
            $this->assertEquals($v, $constants[$k]);
        }
        $this->assertNull($class->getConstructor());
    }

    /**
     * Test the error system of the class.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::setError
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::hasErrored
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::getErrorMessage
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::getErrorTokens
     */
    public function testError()
    {
        /* @var \Bairwell\Hydrator\Annotations\TypeCast\CastBase $sut */
        $sut = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $this->assertFalse($sut->hasErrored());
        $this->assertInternalType('array', $sut->getErrorTokens());
        $this->assertEmpty($sut->getErrorTokens());
        try {
            $sut->getErrorMessage();
            $this->fail('Should through exception as error message should be null');
        } catch (\TypeError $e) {
            $this->assertRegExp('/null/', $e->getMessage());
        }
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('setError');
        $method->setAccessible(true);
        $this->assertSame($sut, $method->invokeArgs($sut, ['testing error', ['a' => 1, 'cat' => 'annoying']]));
        $this->assertTrue($sut->hasErrored());
        $this->assertInternalType('array', $sut->getErrorTokens());
        $this->assertEquals(['a' => 1, 'cat' => 'annoying'], $sut->getErrorTokens());
        $this->assertEquals('testing error', $sut->getErrorMessage());
    }

    /**
     * Test the cast system of the class.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::cast
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\CastBase::setError
     */
    public function testCastInvalidNull()
    {
        /* @var \Bairwell\Hydrator\Annotations\TypeCast\CastBase $sut */
        $sut = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        // try with invalid null
        $sut->allowNull = 'abc';
        try {
            $sut->cast('abc');
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('AllowNull is not boolean on cast', $e->getMessage());
        }
    }

    /**
     * Test the cast system of the class.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::cast
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\CastBase::setError
     */
    public function testCastNullNotAllowed()
    {
        /* @var \Bairwell\Hydrator\Annotations\TypeCast\CastBase $sut */
        $sut            = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $sut->allowNull = false;
        try {
            $sut->cast('abc');
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('If the defaultValue is null, nulls must be allowed on cast', $e->getMessage());
        }
        try {
            $sut->cast('abc', null);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('If the defaultValue is null, nulls must be allowed on cast', $e->getMessage());
        }
    }

    /**
     * Test the cast system of the class.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::cast
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\CastBase::setError
     */
    public function testCastNullWithNull()
    {
        /* @var \Bairwell\Hydrator\Annotations\TypeCast\CastBase $sut */
        $sut            = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $sut->allowNull = true;
        $this->assertNull($sut->cast(null));
        $this->assertNull($sut->cast(null, null));
    }

    /**
     * Test the cast system of the class.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\CastBase::cast
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\CastBase::setError
     */
    public function testCast()
    {
        $sut = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $sut->expects($this->any())
            ->method('doCast')
            ->withConsecutive(['a', null], ['b', null], ['c', 123])
            ->willReturnOnConsecutiveCalls('returnA', 'returnB', 'returnC');
        /* @var \Bairwell\Hydrator\Annotations\TypeCast\CastBase $sut */
        $sut->allowNull = true;
        $this->assertEquals('returnA', $sut->cast('a'));
        $this->assertEquals('returnB', $sut->cast('b', null));
        $this->assertEquals('returnC', $sut->cast('c', 123));
    }
}
