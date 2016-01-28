<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class AsArrayTest.
 * @uses \Bairwell\Hydrator\Annotations\AsArray
 * @uses \Bairwell\Hydrator\Annotations\AsBase
 */
class AsArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations() {
        $sut=new AsArray();
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
        $sut=new AsArray();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsArray',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\AsBase',$sut);
        $this->assertTrue($sut->allowNull);
    }

    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsArray::doCast
     */
    public function testCast() {
        $sut=new AsArray();
        $value='x';
        $this->assertEquals(['x'],$sut->cast($value));
        $value=['y'];
        $this->assertEquals(['y'],$sut->cast($value));
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\AsArray::doCast
     */
    public function testCastInvalidDefault() {
        $sut=new AsArray();
        try {
            $sut->cast('abc','abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be an array for AsArray casts',$e->getMessage());
        }
    }
}
