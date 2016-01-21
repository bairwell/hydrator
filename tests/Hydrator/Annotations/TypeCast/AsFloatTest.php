<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Class AsFloatTest.
 * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsFloat
 * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
 */
class AsFloatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations()
    {
        $sut        = new AsFloat();
        $reflection = new \ReflectionClass($sut);
        $comments   = $reflection->getDocComment();
        $expected   = preg_quote('@Annotation');
        $results    = preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches);;
        $this->assertEquals(1, $results);
        $expected = preg_quote('@Target({"PROPERTY"})');
        $this->assertEquals(1, preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        $property = $reflection->getProperty('allowNull');
        $comments = $property->getDocComment();
        $expected = preg_quote('@var ').'(bool|boolean)';
        $this->assertEquals(1, preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        //
        $property = $reflection->getProperty('precision');
        $comments = $property->getDocComment();
        $expected = preg_quote('@var ').'(int|integer)';
        $this->assertEquals(1, preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        //
        $property = $reflection->getProperty('decimalSeparator');
        $comments = $property->getDocComment();
        $expected = preg_quote('@var ').'(str|string)';
        $this->assertEquals(1, preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        //
        $property = $reflection->getProperty('digitsSeparator');
        $comments = $property->getDocComment();
        $expected = preg_quote('@var ').'(str|string)';
        $this->assertEquals(1, preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
    }
    /**
     * Testing inheritance.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat
     */
    public function testConstructor() {
        $sut=new AsFloat();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\TypeCast\AsFloat',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\TypeCast\CastBase',$sut);
        $this->assertEquals('.',$sut->decimalSeparator);
        $this->assertEquals(',',$sut->digitsSeparator);
        $this->assertNull($sut->precision);
        $this->assertTrue($sut->allowNull);

    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastDefaults()
    {
        $sut=new AsFloat();
        $this->assertEquals(1.43,$sut->cast('xyz',1.43),'Cast defaults',0.0001);
        $this->assertTrue($sut->hasErrored());
        $this->assertNull($sut->cast('xyz'));
        $this->assertTrue($sut->hasErrored());
        $this->assertEquals(32.21,$sut->cast(32.21,1.43),'Cast defaults',0.0001);
        $this->assertFalse($sut->hasErrored());

        try {
            $sut->cast(234.12,'abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be a float for AsFloat casts',$e->getMessage());
        }
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCast()
    {
        $sut    = new AsFloat();
        $values = [
            '1'                           => floatval(1),
            '0'                           => floatval(0),
            '1.34'                        => floatval(1.34),
            '3.1417'                      => floatval(3.1417),
            '67'                          => floatval(67),
            '12984392.32324234452'        => floatval(12984392.32324234452),
            '-123'                        => floatval(- 123),
            '1.2e3'                       => floatval(1.2e3),
            '7e-10'                       => floatval(7e-10),
            '-34323.23'                   => floatval(- 34323.23),
            '123,346.56'                  => floatval(123346.56),
            '356,123,346.56'              => floatval(356123346.56),
            '456,123,346.543,435,435,346' => floatval(456123346.543435435346)
        ];
        foreach ($values as $value => $expected) {
            $this->assertEquals($expected, $sut->cast($value), 'Comparing floats of '.$value, 0.0001);
            $this->assertFalse($sut->hasErrored());
        }
        // check invalids
        $invalidValues = ['xyz', 'abc', '1.2.3.4', '1,234.234.23'];
        foreach ($invalidValues as $value) {
            $this->assertNull($sut->cast($value), 'When floating invalid value');
            $this->assertTrue($sut->hasErrored());
            $this->assertSame(CastBase::FLOAT_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
            $this->assertEquals(['decimalSeparator'=>'.','digitsSeparator'=>','],$sut->getErrorTokens());
            // with different default
            $this->assertEquals(1.4323534, $sut->cast($value,1.4323534), 'Comparing floats of '.$value, 0.0001);
            $this->assertTrue($sut->hasErrored());
            $this->assertSame(CastBase::FLOAT_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
            $this->assertEquals(['decimalSeparator'=>'.','digitsSeparator'=>','],$sut->getErrorTokens());
        }
    }

    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastInvalidFormat()
    {
        $sut=new AsFloat();
        $this->assertNull($sut->cast([]), 'When floating invalid format value');
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEquals([],$sut->getErrorTokens());
        // with different default
        $this->assertEquals(273.123454,$sut->cast([],273.123454), 'When floating invalid format value', 0.0001);
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEquals([],$sut->getErrorTokens());
    }

    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastDifferentThousands()
    {
        $sut = new AsFloat();
        $sut->digitsSeparator='j';
        $expected=floatval('12345623.45');
        $value=$sut->cast('123j456j23.45');
        $this->assertEquals($expected, $value, 'Comparing floats', 0.0001);
    }


    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastDifferentDecimals()
    {
        $sut = new AsFloat();
        $sut->decimalSeparator='Q';
        $value=$sut->cast('123,456,23Q45');
        $expected=floatval('12345623.45');
        $this->assertEquals($expected, $value, 'Comparing floats', 0.0001);
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastRounding() {
        $sut=new AsFloat();
        $sut->precision=0;
        $this->assertEquals(3,$sut->cast(3.4343),0.0000001);
        $sut->precision=1;
        $this->assertEquals(3.4,$sut->cast(3.4343),0.0000001);
        $sut->precision=2;
        $this->assertEquals(3.43,$sut->cast(3.4343),0.0000001);
        $sut->precision=3;
        $this->assertEquals(3.434,$sut->cast(3.43436),0.0000001);
        $sut->precision=4;
        $this->assertEquals(3.4344,$sut->cast(3.43436),0.0000001);
        $sut->precision=5;
        $this->assertEquals(3.43436,$sut->cast(3.43436),0.0000001);
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsFloat::cast
     */
    public function testCastRoundingStrings() {
        $sut=new AsFloat();
        $sut->precision=0;
        //$this->assertEquals(3,$sut->cast(3.4343),0.0000001);
        $sut->precision=1;
        $this->assertEquals(3.4,$sut->cast('3.4343'),0.0000001);
        $sut->precision=2;
        $this->assertEquals(3.43,$sut->cast('3.4343'),0.0000001);
        $sut->precision=3;
        $this->assertEquals(3.434,$sut->cast('3.43436'),0.0000001);
        $sut->precision=4;
        $this->assertEquals(3.4344,$sut->cast('3.43436'),0.0000001);
        $sut->precision=5;
        $this->assertEquals(3.43436,$sut->cast('3.43436'),0.0000001);
    }

}
