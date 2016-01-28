<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class AsIntTest.
 * @uses \Bairwell\Hydrator\Annotations\AsInt
 * @uses \Bairwell\Hydrator\Annotations\AsBase
 */
class AsIntTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations()
    {
        $sut        = new AsInt();
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
        $property = $reflection->getProperty('max');
        $comments = $property->getDocComment();
        $expected = preg_quote('@var ').'(int|integer)';
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
     * @covers \Bairwell\Hydrator\Annotations\AsInt
     */
    public function testConstructor() {
        $sut=new AsInt();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsInt',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsBase',$sut);
        $this->assertEquals(PHP_INT_MIN,$sut->min);
        $this->assertEquals(PHP_INT_MAX,$sut->max);
        $this->assertEquals(',',$sut->digitsSeparator);
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastDefaults()
    {
        $sut=new AsInt();
        $this->assertEquals(2,$sut->cast('test',2));
        $this->assertTrue($sut->hasErrored());
        $this->assertEquals(43,$sut->cast(43,2));
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast('xyz'));
        $this->assertTrue($sut->hasErrored());
        try {
            $sut->cast('abc',1.23);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be an Int for AsInt casts',$e->getMessage());
        }
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastMinMax()
    {
        $sut=new AsInt();
        $sut->min=1.3;
        try {
            $sut->cast('abc',5);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('AsInt min/max are invalid - must be ints',$e->getMessage());
        }
        $sut->min=3;
        $sut->max=453.32;
        try {
            $sut->cast('abc',5);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('AsInt min/max are invalid - must be ints',$e->getMessage());
        }
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastMinMaxDefault()
    {
        $sut=new AsInt();
        $sut->min=5;
        $sut->max=10;
        $this->assertEquals(5,$sut->cast(5,5));

        try {
            $sut->cast(5,4);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Default value is not within min/max range for AsInt',$e->getMessage());
        }
        try {
            $sut->cast(5,11);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Default value is not within min/max range for AsInt',$e->getMessage());
        }
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastInvalidValue() {
        $sut=new AsInt();
        $this->assertFalse($sut->hasErrored());
        $this->assertEquals(4,$sut->cast([],4),'Cast with default provided');
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        //
        $this->assertNull($sut->cast(true),'Cast with no default');
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // ensure it still works
        $this->assertEquals(27,$sut->cast(27,32));
        $this->assertFalse($sut->hasErrored());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastNotANumber() {
        $sut=new AsInt();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast('hello'));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::DECIMAL_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastNotAnInt() {
        $sut=new AsInt();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast(PHP_INT_MAX+PHP_INT_MAX));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::DECIMAL_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::cast
     */
    public function testCastOutsideRange() {
        $sut=new AsInt();
        $sut->min=23;
        $sut->max=30;
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast(22));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::DECIMAL_OUTSIDE_ACCEPTABLE_RANGE,$sut->getErrorMessage());
        $this->assertEquals(['%min%'=>23,'%max%'=>30],$sut->getErrorTokens());
    }

}
