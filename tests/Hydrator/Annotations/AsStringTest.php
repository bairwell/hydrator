<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class AsStringTest.
 * @uses \Bairwell\Hydrator\Annotations\AsString
 * @uses \Bairwell\Hydrator\Annotations\AsBase
 */
class AsStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations() {
        $sut=new AsString();
        $reflection=new \ReflectionClass($sut);
        $comments=$reflection->getDocComment();
        $expected=preg_quote('@Annotation');
        $results=preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches);;
        $this->assertEquals(1,$results);
        $expected=preg_quote('@Target({"PROPERTY"})');
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        $property=$reflection->getProperty('allowNull');
        $comments=$property->getDocComment();
        $expected=preg_quote('@var ').'(bool|boolean)';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
    }

    /**
     * Testing inheritance.
     *
     * @test
     */
    public function testInheritence() {
        $sut=new AsString();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsString',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsBase',$sut);
        $this->assertTrue($sut->allowNull);
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsString::cast
     */
    public function testCastInvalidDefault() {
        $sut=new AsString();
        try {
            $sut->cast('abx',123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be a string for AsString casts',$e->getMessage());
        }
    }
    /**
     * Test unmatched values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsString::doCast
     */
    public function testCastTypes() {
        $sut=new AsString();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast([]));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // same again, but with a value.
        $this->assertEquals('hi',$sut->cast([],'hi'));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // and ensure errors are cleared afterwards
        $this->assertEquals('',$sut->cast(''));
        $this->assertFalse($sut->hasErrored());
    }

    /**
     * Test matched values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsString::doCast
     */
    public function testCast()
    {
        $sut = new AsString();
        $this->assertFalse($sut->hasErrored());
        $result=$sut->cast('abc');
        $this->assertInternalType('string',$result);
        $this->assertEquals('abc',$result);
        $result=$sut->cast(123);
        $this->assertInternalType('string',$result);
        $this->assertEquals('123',$result);
    }
}
