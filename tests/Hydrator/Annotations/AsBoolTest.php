<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class AsBoolTest.
 * @uses \Bairwell\Hydrator\Annotations\asBool
 * @uses \Bairwell\Hydrator\Annotations\AsBase
 */
class AsBoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations() {
        $sut=new AsBool();
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
        $sut=new AsBool();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsBool',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsBase',$sut);
        $this->assertTrue($sut->allowNull);

    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsInt::doCast
     */
    public function testCastInvalidDefault() {
        $sut=new AsBool();
        try {
            $sut->cast('abx','abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be a boolean for AsBool casts: got string',$e->getMessage());
        }
    }
    /**
    /**
     * Test cast with true values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsBool::doCast
     */
    public function testCastTrues()
    {
        $sut   = new AsBool();
        $this->assertTrue($sut->cast(true));
        $this->assertFalse($sut->hasErrored());

        $this->assertTrue($sut->cast(1));
        $this->assertFalse($sut->hasErrored());

        $this->assertTrue($sut->cast('TrUe'));
        $this->assertFalse($sut->hasErrored());

        $this->assertTrue($sut->cast('on'));
        $this->assertFalse($sut->hasErrored());

        $this->assertTrue($sut->cast('YeS'));
        $this->assertFalse($sut->hasErrored());
    }

    /**
     * Test cast with false values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsBool::doCast
     */
    public function testCastFalses()
    {
        $sut   = new AsBool();
        $this->assertFalse($sut->cast(false));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast(0));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast('0'));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast('fAlse'));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast('ofF'));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast('nO'));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast('0'));
        $this->assertFalse($sut->hasErrored());

        $this->assertFalse($sut->cast(''));
        $this->assertFalse($sut->hasErrored());
    }
    /**
     * Test unmatched values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsBool::doCast
     */
    public function testCastTypes() {
        $sut=new AsBool();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast([]));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_BOOLS_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // same again, but with a value.
        $this->assertTrue($sut->cast([],true));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::ONLY_BOOLS_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // and ensure errors are cleared afterwards
        $this->assertFalse($sut->cast(''));
        $this->assertFalse($sut->hasErrored());

    }
    /**
     * Test unmatched values.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsBool::doCast
     */
    public function testCastUnmatched()
    {
        $sut   = new AsBool();
        $this->assertFalse($sut->hasErrored());
        // with default
        $this->assertFalse($sut->cast('thing',false));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::BOOL_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEquals(['%trues%' => '1,true,on,yes','%falses%' => '0,false,off,no'],$sut->getErrorTokens());
        // without default
        $this->assertNull($sut->cast('thing'));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(AsBase::BOOL_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEquals(['%trues%' => '1,true,on,yes','%falses%' => '0,false,off,no'],$sut->getErrorTokens());
    }
}
